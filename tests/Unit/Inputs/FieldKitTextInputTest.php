<?php

declare(strict_types=1);

use Ameax\FieldkitCore\Inputs\FieldKitTextInput;

it('returns correct name', function () {
    $input = new FieldKitTextInput;

    expect($input->getName())->toBe('text');
});

it('returns correct label', function () {
    $input = new FieldKitTextInput;

    expect($input->getLabel())->toBe('Text');
});

it('returns correct icon', function () {
    $input = new FieldKitTextInput;

    expect($input->getIcon())->toBe('pencil');
});

it('returns correct compatibility group', function () {
    $input = new FieldKitTextInput;

    expect($input->getCompatibilityGroup())->toBe('text');
});

it('returns default validation rules', function () {
    $input = new FieldKitTextInput;

    expect($input->getDefaultValidationRules())->toBe(['string']);
});

it('returns configurable attributes', function () {
    $input = new FieldKitTextInput;
    $attributes = $input->getConfigurableAttributes();

    expect($attributes)->toHaveKey('placeholder');
    expect($attributes)->toHaveKey('max_length');
    expect($attributes['placeholder']['type'])->toBe('text');
    expect($attributes['max_length']['type'])->toBe('number');
});
