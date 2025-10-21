<?php

declare(strict_types=1);

use Ameax\FieldkitCore\Inputs\FieldKitTextInput;

it('returns correct type', function () {
    $input = new FieldKitTextInput;

    expect($input->getType())->toBe('text');
});

it('returns correct label', function () {
    $input = new FieldKitTextInput;

    expect($input->getLabel())->toBe('Text Input');
});

it('returns correct icon', function () {
    $input = new FieldKitTextInput;

    expect($input->getIcon())->toBe('heroicon-o-pencil');
});

it('returns correct compatibility group', function () {
    $input = new FieldKitTextInput;

    expect($input->getCompatibilityGroup())->toBe('text');
});

it('validates input correctly', function () {
    $input = new FieldKitTextInput;

    expect($input->validateInput('test value'))->toBeTrue();
    expect($input->validateInput(''))->toBeTrue(); // Empty is valid
    expect($input->validateInput(123))->toBeFalse(); // Non-string
    expect($input->validateInput(null))->toBeFalse(); // Null
});

it('formats output correctly', function () {
    $input = new FieldKitTextInput;

    expect($input->formatOutput('test value'))->toBe('test value');
    expect($input->formatOutput(''))->toBe('');
});

it('is compatible with same group', function () {
    $textInput = new FieldKitTextInput;
    $emailInput = new \Ameax\FieldkitCore\Inputs\FieldKitEmailInput;

    expect($textInput->isCompatibleWith($emailInput))->toBeTrue();
});

it('is not compatible with different group', function () {
    $textInput = new FieldKitTextInput;
    $numberInput = new \Ameax\FieldkitCore\Inputs\FieldKitNumberInput;

    expect($textInput->isCompatibleWith($numberInput))->toBeFalse();
});
