<?php

declare(strict_types=1);

it('can run tests', function () {
    expect(true)->toBeTrue();
});

it('has config set up', function () {
    $config = config('fieldkit.input_types');
    expect($config)->toBeArray();
    expect($config)->toHaveKey('text');
});