<?php

declare(strict_types=1);

use Ameax\FieldkitCore\FieldKitInputRegistry;
use Ameax\FieldkitCore\Inputs\FieldKitEmailInput;
use Ameax\FieldkitCore\Inputs\FieldKitTextInput;

it('can register input types from config', function () {
    $registry = app(FieldKitInputRegistry::class);

    expect($registry->has('text'))->toBeTrue();
    expect($registry->has('email'))->toBeTrue();
    expect($registry->has('nonexistent'))->toBeFalse();
});

it('can get input class by type', function () {
    $registry = app(FieldKitInputRegistry::class);

    expect($registry->get('text'))->toBe(FieldKitTextInput::class);
    expect($registry->get('email'))->toBe(FieldKitEmailInput::class);
});

it('throws exception for nonexistent input type', function () {
    $registry = app(FieldKitInputRegistry::class);

    $registry->get('nonexistent');
})->throws(InvalidArgumentException::class, 'Input type nonexistent not found in registry');

it('can register new input type', function () {
    $registry = app(FieldKitInputRegistry::class);

    $registry->register('custom', FieldKitTextInput::class);

    expect($registry->has('custom'))->toBeTrue();
    expect($registry->get('custom'))->toBe(FieldKitTextInput::class);
});

it('validates input class implements interface', function () {
    $registry = app(FieldKitInputRegistry::class);

    $registry->register('invalid', stdClass::class);
})->throws(InvalidArgumentException::class, 'stdClass must implement FieldKitInputInterface');

it('returns all registered types', function () {
    $registry = app(FieldKitInputRegistry::class);

    $types = $registry->all();

    expect($types)->toBeArray();
    expect($types)->toHaveKey('text');
    expect($types)->toHaveKey('email');
    expect(count($types))->toBe(7); // 7 MVP types
});

it('returns options for admin interface', function () {
    $registry = app(FieldKitInputRegistry::class);

    $options = $registry->getOptionsForAdmin();

    expect($options)->toBeArray();
    expect($options)->toHaveKey('text', 'Text');
    expect($options)->toHaveKey('email', 'Email');
});
