<?php

declare(strict_types=1);

use Ameax\FieldkitCore\Contracts\FieldKitAdapterInterface;
use Ameax\FieldkitCore\FieldKitInputRegistry;
use Ameax\FieldkitCore\Models\FieldKitDefinition;
use Ameax\FieldkitCore\Models\FieldKitForm;
use Ameax\FieldkitCore\Models\FieldKitOption;
use Ameax\FieldkitCore\Services\FieldKitDefinitionResolver;
use Ameax\FieldkitCore\Services\FieldKitService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new FieldKitService(
        app(FieldKitInputRegistry::class),
        app(FieldKitDefinitionResolver::class)
    );
});

it('gets fields for a purpose token', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'test_form',
        'name' => 'Test Form',
        'is_active' => true,
    ]);

    $field1 = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'field1',
        'type' => 'text',
        'label' => 'Field 1',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $field2 = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'field2',
        'type' => 'email',
        'label' => 'Field 2',
        'is_active' => false, // inactive
        'sort_order' => 2,
    ]);

    $fields = $this->service->getFieldsForPurpose('test_form');

    expect($fields)->toHaveCount(1);
    expect($fields->first()->key)->toBe('field1');
});

it('returns empty collection for non-existent purpose', function () {
    $fields = $this->service->getFieldsForPurpose('non_existent');

    expect($fields)->toBeEmpty();
});

it('renders form components with adapter', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'test_render',
        'name' => 'Test Render Form',
        'is_active' => true,
    ]);

    $field = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'test_field',
        'type' => 'text',
        'label' => 'Test Field',
        'placeholder' => 'Enter text',
        'description' => 'This is a test field',
        'is_required' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $adapter = Mockery::mock(FieldKitAdapterInterface::class);
    $adapter->shouldReceive('supports')->with('text')->andReturn(true);

    $components = $this->service->renderFormComponents('test_render', $adapter);

    expect($components)->toBeArray();
});

it('skips fields with unmet conditions', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'conditional_form',
        'name' => 'Conditional Form',
        'is_active' => true,
    ]);

    $field = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'conditional_field',
        'type' => 'text',
        'label' => 'Conditional Field',
        'is_active' => true,
        'conditions' => [
            [
                'field_key' => 'trigger_field',
                'operator' => 'equals',
                'answer_values' => ['show_me'],
            ],
        ],
    ]);

    $adapter = Mockery::mock(FieldKitAdapterInterface::class);
    $adapter->shouldReceive('supports')->andReturn(true);

    // Without trigger field value
    $components = $this->service->renderFormComponents('conditional_form', $adapter, []);
    expect($components)->toBeEmpty();

    // With wrong trigger field value
    $components = $this->service->renderFormComponents('conditional_form', $adapter, ['trigger_field' => 'hide_me']);
    expect($components)->toBeEmpty();

    // With correct trigger field value
    $components = $this->service->renderFormComponents('conditional_form', $adapter, ['trigger_field' => 'show_me']);
    expect($components)->toHaveCount(1);
});

it('stores field values to model', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'store_test',
        'name' => 'Store Test Form',
        'is_active' => true,
    ]);

    $field = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'test_field',
        'type' => 'text',
        'label' => 'Test Field',
        'is_active' => true,
    ]);

    $model = new class extends \Illuminate\Database\Eloquent\Model
    {
        protected $fillable = ['fieldkit_data'];

        protected $casts = ['fieldkit_data' => 'array'];

        public $timestamps = false;
    };

    $this->service->storeFieldValues(
        'store_test',
        ['test_field' => 'test_value', 'ignored_field' => 'ignored'],
        $model
    );

    expect($model->fieldkit_data)->toHaveKey('test_field', 'test_value');
    expect($model->fieldkit_data)->not->toHaveKey('ignored_field');
});

it('handles select field with options', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'select_form',
        'name' => 'Select Form',
        'is_active' => true,
    ]);

    $field = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'select_field',
        'type' => 'select',
        'label' => 'Select Field',
        'is_active' => true,
    ]);

    FieldKitOption::create([
        'fieldkit_definition_id' => $field->id,
        'value' => 'option1',
        'label' => 'Option 1',
        'sort_order' => 1,
    ]);

    FieldKitOption::create([
        'fieldkit_definition_id' => $field->id,
        'value' => 'option2',
        'label' => 'Option 2',
        'sort_order' => 2,
    ]);

    $adapter = Mockery::mock(FieldKitAdapterInterface::class);
    $adapter->shouldReceive('supports')->with('select')->andReturn(true);

    $components = $this->service->renderFormComponents('select_form', $adapter);

    expect($components)->toHaveCount(1);
});
