<?php

namespace Ameax\FieldkitCore;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FieldkitCoreServiceProvider extends PackageServiceProvider
{
    public function register(): void
    {
        parent::register();

        // Input type registry as singleton
        $this->app->singleton(FieldKitInputRegistry::class, function ($app) {
            $registry = new FieldKitInputRegistry;

            // Load input types from config
            foreach (config('fieldkit.input_types', []) as $token => $class) {
                $registry->register($token, $class);
            }

            return $registry;
        });

        // Core services as singletons
        $this->app->singleton(\Ameax\FieldkitCore\Services\FieldKitDefinitionResolver::class);
        $this->app->singleton(\Ameax\FieldkitCore\Services\FieldKitService::class);
        $this->app->singleton(\Ameax\FieldkitCore\Services\FieldKitValidationService::class);
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('fieldkit-core')
            ->hasConfigFile('fieldkit')
            ->hasMigrations([
                'create_fieldkit_forms_table',
                'create_fieldkit_definitions_table',
                'create_fieldkit_options_table',
            ]);
    }
}
