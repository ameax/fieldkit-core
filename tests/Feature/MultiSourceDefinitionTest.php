<?php

declare(strict_types=1);

use Ameax\FieldkitCore\Models\FieldKitForm;
use Ameax\FieldkitCore\Models\FieldKitDefinition;
use Ameax\FieldkitCore\Services\FieldKitDefinitionResolver;

beforeEach(function () {
    $this->resolver = app(FieldKitDefinitionResolver::class);
});

it('loads definitions from database with correct priority', function () {
    $form = FieldKitForm::factory()->create([
        'purpose_token' => 'customer_registration',
    ]);
    
    FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $form->id,
        'field_key' => 'phone',
        'type' => 'text',
        'label' => 'Phone (Database)',
        'is_active' => true,
    ]);
    
    $definitions = $this->resolver->resolveDefinitions('customer_registration');
    
    expect($definitions)->toHaveCount(1);
    expect($definitions[0]->label)->toBe('Phone (Database)');
    expect($definitions[0]->source)->toBe('database');
    expect($definitions[0]->priority)->toBe(100);
});

it('loads definitions from config with higher priority', function () {
    // Set up config-based definitions
    config([
        'fieldkit.definitions.customer_registration' => [
            [
                'field_key' => 'email',
                'type' => 'email',
                'label' => 'Email (Config)',
                'is_required' => true,
                'sort_order' => 1,
            ]
        ]
    ]);
    
    $definitions = $this->resolver->resolveDefinitions('customer_registration');
    
    expect($definitions)->toHaveCount(1);
    expect($definitions[0]->label)->toBe('Email (Config)');
    expect($definitions[0]->source)->toBe('config');
    expect($definitions[0]->priority)->toBe(200);
});

it('merges definitions from multiple sources with correct priority', function () {
    // Database definition
    $form = FieldKitForm::factory()->create([
        'purpose_token' => 'customer_registration',
    ]);
    
    FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $form->id,
        'field_key' => 'phone',
        'type' => 'text',
        'label' => 'Phone (Database)',
        'is_active' => true,
        'sort_order' => 2,
    ]);
    
    // Config definition (higher priority)
    config([
        'fieldkit.definitions.customer_registration' => [
            [
                'field_key' => 'email',
                'type' => 'email',
                'label' => 'Email (Config)',
                'is_required' => true,
                'sort_order' => 1,
            ]
        ]
    ]);
    
    $definitions = $this->resolver->resolveDefinitions('customer_registration');
    
    expect($definitions)->toHaveCount(2);
    
    // Should be sorted by priority (config first), then by sort_order
    expect($definitions[0]->label)->toBe('Email (Config)');
    expect($definitions[0]->source)->toBe('config');
    expect($definitions[1]->label)->toBe('Phone (Database)');
    expect($definitions[1]->source)->toBe('database');
});

it('config definitions override database definitions with same field_key', function () {
    // Database definition
    $form = FieldKitForm::factory()->create([
        'purpose_token' => 'customer_registration',
    ]);
    
    FieldKitDefinition::factory()->create([
        'fieldkit_form_id' => $form->id,
        'field_key' => 'email',
        'type' => 'text',
        'label' => 'Email (Database)',
        'is_active' => true,
    ]);
    
    // Config definition with same field_key (higher priority)
    config([
        'fieldkit.definitions.customer_registration' => [
            [
                'field_key' => 'email',
                'type' => 'email',
                'label' => 'Email (Config Override)',
                'is_required' => true,
            ]
        ]
    ]);
    
    $definitions = $this->resolver->resolveDefinitions('customer_registration');
    
    expect($definitions)->toHaveCount(1);
    expect($definitions[0]->label)->toBe('Email (Config Override)');
    expect($definitions[0]->type)->toBe('email');
    expect($definitions[0]->source)->toBe('config');
});

it('loads definitions from JSON file with correct priority', function () {
    // Create temporary JSON file
    $jsonPath = storage_path('fieldkit/customer_registration.json');
    
    if (!is_dir(dirname($jsonPath))) {
        mkdir(dirname($jsonPath), 0755, true);
    }
    
    $jsonData = [
        [
            'field_key' => 'company',
            'type' => 'text',
            'label' => 'Company (JSON)',
            'sort_order' => 1,
        ]
    ];
    
    file_put_contents($jsonPath, json_encode($jsonData));
    
    // Configure JSON source
    config([
        'fieldkit.definition_sources' => [
            'config' => ['priority' => 200],
            'database' => ['priority' => 100],
            'json' => [
                'priority' => 50,
                'path' => storage_path('fieldkit'),
            ],
        ]
    ]);
    
    $definitions = $this->resolver->resolveDefinitions('customer_registration');
    
    expect($definitions)->toHaveCount(1);
    expect($definitions[0]->label)->toBe('Company (JSON)');
    expect($definitions[0]->source)->toBe('json');
    expect($definitions[0]->priority)->toBe(50);
    
    // Cleanup
    unlink($jsonPath);
});

it('returns empty array for nonexistent form', function () {
    $definitions = $this->resolver->resolveDefinitions('nonexistent_form');
    
    expect($definitions)->toBeArray();
    expect($definitions)->toHaveCount(0);
});