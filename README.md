# FieldKit Core

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ameax/fieldkit-core.svg?style=flat-square)](https://packagist.org/packages/ameax/fieldkit-core)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ameax/fieldkit-core/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ameax/fieldkit-core/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ameax/fieldkit-core/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ameax/fieldkit-core/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ameax/fieldkit-core.svg?style=flat-square)](https://packagist.org/packages/ameax/fieldkit-core)

> **⚠️ Work In Progress**: This package is currently under active development and not yet ready for production use.

Framework-agnostic core package for dynamic, admin-manageable form fields with multi-source support and external system integration.

## Features

- **Multi-Source Support** - Load field definitions from Database, JSON config, or PHP classes
- **Handler System** - Flexible external system integration (Ameax, Mailchimp, APIs) with sync/async processing
- **Auto-Discovery** - Automatically discover and register input types and transformers
- **Dual-Storage** - Local JSON storage + external system handlers
- **Type-Safety** - Compatibility system prevents data loss during field type changes
- **Conditional Visibility** - Show/hide fields based on other field values
- **Transformers** - Safe value transformation (e.g., boolean → "1"/"0" for external systems)
- **Framework-Agnostic** - Core logic independent of UI framework

## Installation

```bash
composer require ameax/fieldkit-core
```

Publish the migrations and config:

```bash
php artisan vendor:publish --tag="fieldkit-core-migrations"
php artisan migrate

php artisan vendor:publish --tag="fieldkit-core-config"
```

## Basic Usage

### 1. Configure Form with Handlers

```php
// config/fieldkit-forms.php
return [
    'customer_registration' => [
        'model' => \App\Models\Customer::class,
        'json_column' => 'fieldkit_data',
        'handlers' => [
            \App\FieldKit\Handlers\AmeaxCustomerHandler::class,
        ],
        'fields' => [
            [
                'key' => 'newsletter',
                'type' => 'checkbox',
                'label' => 'Newsletter subscription',
                'store_in_json' => true,
                'mappings' => [
                    [
                        'adapter' => 'ameax_column',
                        'target_table' => 'customer',
                        'target_column' => 'xcu_newsletter',
                        'transformer' => 'boolean',
                    ],
                ],
            ],
        ],
    ],
];
```

### 2. Store Form Values

```php
use Ameax\FieldkitCore\Services\FieldKitService;

$service = app(FieldKitService::class);

$service->storeFieldValues(
    purposeToken: 'customer_registration',
    formData: ['newsletter' => true],
    model: $customer
);

// Result:
// 1. Local: $customer->fieldkit_data['newsletter'] = true (persistent)
// 2. Handler: AmeaxCustomerHandler triggered (queued)
```

### 3. Create a Handler

```php
<?php

namespace App\FieldKit\Handlers;

use Ameax\FieldkitCore\Contracts\FieldKitMappingHandlerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AmeaxCustomerHandler implements FieldKitMappingHandlerInterface
{
    public function supports(string $adapter): bool
    {
        return $adapter === 'ameax_column';
    }

    public function shouldQueue(): bool
    {
        return true;  // Run async via queue
    }

    public function handle(Model $model, Collection $mappings, array $formData): void
    {
        // Process mappings and sync to external system
        $data = $this->transformMappings($mappings, $formData);
        $this->ameax->updateCustomer($model->id, $data);
    }
}
```

## Documentation

For full documentation, see the [FieldKit Documentation](https://github.com/ameax/fieldkit-core/tree/main/docs).

## UI Integration

For Filament admin panel integration, install:

```bash
composer require ameax/fieldkit-filament
```

See [fieldkit-filament](https://github.com/ameax/fieldkit-filament) for details.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Michael Schmidt](https://github.com/ms-aranes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
