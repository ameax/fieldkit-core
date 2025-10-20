<?php

declare(strict_types=1);

use Ameax\FieldkitCore\Models\FieldKitForm;
use Ameax\FieldkitCore\Models\FieldKitDefinition;
use Ameax\FieldkitCore\Models\FieldKitOption;
use Ameax\FieldkitCore\Services\FieldKitService;
use Ameax\FieldkitCore\Data\FieldKitFormData;
use Ameax\FieldkitCore\Jobs\ProcessFieldKitMapping;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    
    $this->form = FieldKitForm::factory()->create([
        'purpose_token' => 'customer_registration',
        'is_active' => true,
    ]);
    
    $this->service = app(FieldKitService::class);
});

it('retrieves form definitions by purpose token', function () {
    FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'field_key' => 'phone',
        'type' => 'text',
        'is_active' => true,
        'sort_order' => 1,
    ]);
    
    FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'field_key' => 'newsletter',
        'type' => 'checkbox',
        'is_active' => false, // Inactive
        'sort_order' => 2,
    ]);
    
    $formData = $this->service->getForm('customer_registration');
    
    expect($formData)->toBeInstanceOf(FieldKitFormData::class);
    expect($formData->form->purpose_token)->toBe('customer_registration');
    expect($formData->definitions)->toHaveCount(1); // Only active
    expect($formData->definitions[0]->field_key)->toBe('phone');
});

it('applies conditional visibility when retrieving definitions', function () {
    FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'field_key' => 'phone',
        'type' => 'text',
        'is_active' => true,
        'sort_order' => 1,
        'conditions' => null, // Always visible
    ]);
    
    FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'field_key' => 'company_phone',
        'type' => 'text',
        'is_active' => true,
        'sort_order' => 2,
        'conditions' => [
            [
                'field_type' => 'native',
                'field_key' => 'customer_type',
                'operator' => 'equals',
                'expected_values' => ['business']
            ]
        ],
    ]);
    
    // Without context - company_phone should be hidden
    $formData = $this->service->getForm('customer_registration');
    expect($formData->definitions)->toHaveCount(1);
    expect($formData->definitions[0]->field_key)->toBe('phone');
    
    // With business context - both fields visible
    $formDataWithContext = $this->service->getForm('customer_registration', ['customer_type' => 'business']);
    expect($formDataWithContext->definitions)->toHaveCount(2);
});

it('stores form data with automatic JSON storage', function () {
    $definition = FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'field_key' => 'phone',
        'type' => 'text',
        'is_active' => true,
    ]);
    
    $customer = \App\Models\Customer::factory()->create();
    
    $data = [
        'phone' => '+49 123 456789',
    ];
    
    $this->service->storeData('customer_registration', $customer, $data);
    
    $customer->refresh();
    
    expect($customer->fieldkit_data)->toBe(['phone' => '+49 123 456789']);
});

it('processes external mappings when storing data', function () {
    $definition = FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'field_key' => 'phone',
        'type' => 'text',
        'is_active' => true,
        'external_mappings' => [
            [
                'adapter_type' => 'ameax_column',
                'target' => 'customer.phone',
                'config' => []
            ]
        ],
    ]);
    
    $customer = \App\Models\Customer::factory()->create();
    
    $data = [
        'phone' => '+49 123 456789',
    ];
    
    $this->service->storeData('customer_registration', $customer, $data);
    
    Queue::assertPushed(ProcessFieldKitMapping::class, function ($job) {
        return $job->adapterType === 'ameax_column' &&
               $job->target === 'customer.phone' &&
               $job->value === '+49 123 456789';
    });
});

it('validates form data correctly', function () {
    $definition = FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'field_key' => 'email',
        'type' => 'email',
        'is_active' => true,
        'is_required' => true,
        'validation_rules' => 'email|max:255',
    ]);
    
    // Valid data
    $validData = ['email' => 'test@example.com'];
    $result = $this->service->validateData('customer_registration', $validData);
    expect($result->passes())->toBeTrue();
    
    // Invalid email format
    $invalidData = ['email' => 'invalid-email'];
    $result = $this->service->validateData('customer_registration', $invalidData);
    expect($result->fails())->toBeTrue();
    expect($result->errors()->has('email'))->toBeTrue();
    
    // Missing required field
    $missingData = [];
    $result = $this->service->validateData('customer_registration', $missingData);
    expect($result->fails())->toBeTrue();
    expect($result->errors()->has('email'))->toBeTrue();
});

it('handles select field options correctly', function () {
    $definition = FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'field_key' => 'category',
        'type' => 'select',
        'is_active' => true,
    ]);
    
    FieldKitOption::factory()->create([
        'fieldkit_definition_id' => $definition->id,
        'value' => 'electronics',
        'label' => 'Electronics',
        'sort_order' => 1,
    ]);
    
    FieldKitOption::factory()->create([
        'fieldkit_definition_id' => $definition->id,
        'value' => 'clothing',
        'label' => 'Clothing',
        'sort_order' => 2,
    ]);
    
    $formData = $this->service->getForm('customer_registration');
    
    $selectDefinition = $formData->definitions[0];
    expect($selectDefinition->options)->toHaveCount(2);
    expect($selectDefinition->options[0]->value)->toBe('electronics');
    expect($selectDefinition->options[1]->value)->toBe('clothing');
});

it('throws exception for nonexistent form', function () {
    $this->service->getForm('nonexistent_form');
})->throws(InvalidArgumentException::class, 'Form with purpose token nonexistent_form not found');