<?php

declare(strict_types=1);

use Ameax\FieldkitCore\Models\FieldKitDefinition;
use Ameax\FieldkitCore\Models\FieldKitForm;
use Ameax\FieldkitCore\Models\FieldKitOption;

beforeEach(function () {
    $this->form = FieldKitForm::factory()->create();
});

it('belongs to a form', function () {
    $definition = FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
    ]);
    
    expect($definition->form)->toBeInstanceOf(FieldKitForm::class);
    expect($definition->form->id)->toBe($this->form->id);
});

it('has many options', function () {
    $definition = FieldKitDefinition::factory()->create([
        'type' => 'select',
        'fieldkit_form_id' => $this->form->id,
    ]);
    
    $option = FieldKitOption::factory()->create([
        'fieldkit_definition_id' => $definition->id,
    ]);
    
    expect($definition->options)->toHaveCount(1);
    expect($definition->options->first())->toBeInstanceOf(FieldKitOption::class);
});

it('filters active definitions', function () {
    FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'is_active' => true,
    ]);
    
    FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'is_active' => false,
    ]);
    
    $active = FieldKitDefinition::active()->get();
    
    expect($active)->toHaveCount(1);
    expect($active->first()->is_active)->toBeTrue();
});

it('evaluates simple condition correctly', function () {
    $definition = FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'conditions' => [
            [
                'field_type' => 'native',
                'field_key' => 'country',
                'operator' => 'in',
                'expected_values' => ['DE', 'AT']
            ]
        ],
    ]);
    
    expect($definition->shouldDisplay(['country' => 'DE']))->toBeTrue();
    expect($definition->shouldDisplay(['country' => 'US']))->toBeFalse();
    expect($definition->shouldDisplay(['country' => 'AT']))->toBeTrue();
});

it('evaluates multiple AND conditions correctly', function () {
    $definition = FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'condition_logic' => 'AND',
        'conditions' => [
            [
                'field_type' => 'native',
                'field_key' => 'country',
                'operator' => 'in',
                'expected_values' => ['DE']
            ],
            [
                'field_type' => 'native',
                'field_key' => 'age',
                'operator' => 'equals',
                'expected_values' => ['18']
            ]
        ],
    ]);
    
    expect($definition->shouldDisplay(['country' => 'DE', 'age' => '18']))->toBeTrue();
    expect($definition->shouldDisplay(['country' => 'DE', 'age' => '17']))->toBeFalse();
    expect($definition->shouldDisplay(['country' => 'US', 'age' => '18']))->toBeFalse();
});

it('evaluates multiple OR conditions correctly', function () {
    $definition = FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'condition_logic' => 'OR',
        'conditions' => [
            [
                'field_type' => 'native',
                'field_key' => 'country',
                'operator' => 'in',
                'expected_values' => ['DE']
            ],
            [
                'field_type' => 'native',
                'field_key' => 'vip',
                'operator' => 'equals',
                'expected_values' => ['1']
            ]
        ],
    ]);
    
    expect($definition->shouldDisplay(['country' => 'DE', 'vip' => '0']))->toBeTrue();
    expect($definition->shouldDisplay(['country' => 'US', 'vip' => '1']))->toBeTrue();
    expect($definition->shouldDisplay(['country' => 'US', 'vip' => '0']))->toBeFalse();
});

it('returns true when no conditions are set', function () {
    $definition = FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'conditions' => null,
    ]);
    
    expect($definition->shouldDisplay())->toBeTrue();
    expect($definition->shouldDisplay(['any' => 'data']))->toBeTrue();
});

it('returns external identifier or value as fallback', function () {
    $definition = FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $this->form->id,
        'type' => 'select',
    ]);
    
    $option1 = FieldKitOption::factory()->create([
        'fieldkit_definition_id' => $definition->id,
        'value' => 'option1',
        'external_identifier' => 'ext123',
    ]);
    
    $option2 = FieldKitOption::factory()->create([
        'fieldkit_definition_id' => $definition->id,
        'value' => 'option2',
        'external_identifier' => null,
    ]);
    
    expect($option1->getExternalIdentifier())->toBe('ext123');
    expect($option2->getExternalIdentifier())->toBe('option2'); // Fallback to value
});