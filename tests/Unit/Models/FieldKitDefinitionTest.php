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
