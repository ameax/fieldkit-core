<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | FieldKit Input Types Registry
    |--------------------------------------------------------------------------
    |
    | This is where you register all available input types. FieldKit will
    | auto-discover these classes and make them available through the
    | registry pattern.
    |
    */
    'input_types' => [
        'text' => \Ameax\FieldkitCore\Inputs\FieldKitTextInput::class,
        'email' => \Ameax\FieldkitCore\Inputs\FieldKitEmailInput::class,
        'number' => \Ameax\FieldkitCore\Inputs\FieldKitNumberInput::class,
        'textarea' => \Ameax\FieldkitCore\Inputs\FieldKitTextareaInput::class,
        'checkbox' => \Ameax\FieldkitCore\Inputs\FieldKitCheckboxInput::class,
        'select' => \Ameax\FieldkitCore\Inputs\FieldKitSelectInput::class,
        'radio' => \Ameax\FieldkitCore\Inputs\FieldKitRadioInput::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Definition Sources
    |--------------------------------------------------------------------------
    |
    | Configure which sources to load field definitions from. Sources are
    | processed in order, with "Config First" priority.
    |
    */
    'definition_sources' => [
        'config' => \Ameax\FieldkitCore\DefinitionSources\ConfigDefinitionSource::class,
        'database' => \Ameax\FieldkitCore\DefinitionSources\DatabaseDefinitionSource::class,
        'json' => \Ameax\FieldkitCore\DefinitionSources\JsonDefinitionSource::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Definitions (Config Source)
    |--------------------------------------------------------------------------
    |
    | Define forms and their fields directly in config. These take priority
    | over database definitions.
    |
    */
    'forms' => [
        // Example form configuration
        // 'customer_registration' => [
        //     'name' => 'Customer Registration',
        //     'description' => 'Additional fields for customer registration',
        //     'handlers' => [
        //         \App\Handlers\AmeaxCustomerHandler::class,
        //     ],
        //     'fields' => [
        //         [
        //             'key' => 'newsletter',
        //             'type' => 'checkbox',
        //             'label' => 'Subscribe to newsletter',
        //             'is_required' => false,
        //             'mappings' => [
        //                 [
        //                     'adapter' => 'ameax_column',
        //                     'target' => 'customer.xcu_newsletter',
        //                 ],
        //             ],
        //         ],
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Handler Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how handlers should be executed and queued.
    |
    */
    'handlers' => [
        'queue_connection' => env('FIELDKIT_QUEUE_CONNECTION', 'default'),
        'queue_name' => env('FIELDKIT_QUEUE_NAME', 'default'),
        'async_by_default' => env('FIELDKIT_ASYNC_HANDLERS', true),
        'retry_attempts' => env('FIELDKIT_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('FIELDKIT_RETRY_DELAY', 60), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automatic JSON storage behavior.
    |
    */
    'storage' => [
        'column_name' => 'fieldkit_data',
        'auto_cast' => true,
    ],
];