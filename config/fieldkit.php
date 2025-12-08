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
        // Example: Customer Registration with dual storage (Ameax + Mailchimp)
        'customer_registration' => [
            'name' => 'Customer Registration',
            'description' => 'Additional fields for customer registration',
            'handlers' => [
                \Ameax\FieldkitCore\Handlers\AmeaxCustomerHandler::class,
                \Ameax\FieldkitCore\Handlers\MailchimpSubscriberHandler::class,
            ],
            'fields' => [
                [
                    'key' => 'newsletter',
                    'type' => 'checkbox',
                    'label' => 'Subscribe to newsletter',
                    'is_required' => false,
                    'mappings' => [
                        [
                            'adapter' => 'ameax_column',
                            'target' => 'customer.xcu_newsletter',
                            'field_key' => 'newsletter',
                            'config' => ['type' => 'boolean'],
                        ],
                        [
                            'adapter' => 'mailchimp_subscriber',
                            'target' => 'subscription',
                            'field_key' => 'newsletter',
                            'config' => [
                                'list_id' => env('MAILCHIMP_NEWSLETTER_LIST_ID'),
                                'status' => 'subscribed',
                                'tags' => [
                                    ['field' => 'newsletter', 'name' => 'Newsletter Subscriber'],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'phone',
                    'type' => 'text',
                    'label' => 'Phone Number',
                    'is_required' => false,
                    'mappings' => [
                        [
                            'adapter' => 'ameax_column',
                            'target' => 'customer.phone',
                            'field_key' => 'phone',
                        ],
                    ],
                ],
            ],
        ],

        // Example: Newsletter signup (Mailchimp only)
        'newsletter_signup' => [
            'name' => 'Newsletter Signup',
            'description' => 'Simple newsletter subscription form',
            'handlers' => [
                \Ameax\FieldkitCore\Handlers\MailchimpSubscriberHandler::class,
            ],
            'fields' => [
                [
                    'key' => 'email',
                    'type' => 'email',
                    'label' => 'Email Address',
                    'is_required' => true,
                    'mappings' => [
                        [
                            'adapter' => 'mailchimp_subscriber',
                            'target' => 'subscription',
                            'field_key' => 'email',
                            'config' => [
                                'status' => 'pending', // Double opt-in
                                'tags' => [
                                    ['name' => 'Website Signup'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
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

        // Mailchimp Handler Configuration
        'mailchimp' => [
            'api_key' => env('MAILCHIMP_API_KEY'),
            'data_center' => env('MAILCHIMP_DATA_CENTER'), // e.g., 'us1', 'us2', 'eu1'
            'default_list_id' => env('MAILCHIMP_DEFAULT_LIST_ID'),
        ],
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

    /*
    |--------------------------------------------------------------------------
    | Context Configuration
    |--------------------------------------------------------------------------
    |
    | Configure context-based form filtering. This allows forms to be shown
    | only in specific contexts (e.g., specific shop groups, regions, etc.)
    |
    | When enabled, apps must provide:
    | - provider: Implements ContextProviderInterface (admin UI fields)
    | - resolver: Implements ContextResolverInterface (runtime filtering)
    |
    */
    'context' => [
        'enabled' => env('FIELDKIT_CONTEXT_ENABLED', false),
        'provider' => null, // App must set: e.g., App\FieldKit\ShopGroupContextProvider::class
        'resolver' => null, // App must set: e.g., App\FieldKit\ShopGroupContextResolver::class
    ],
];
