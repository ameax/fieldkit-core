<?php

namespace Ameax\FieldkitCore\Tests;

use Ameax\FieldkitCore\FieldkitCoreServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            // For test models
            if (str_starts_with($modelName, 'Tests\\Support\\')) {
                return 'Tests\\Support\\'.class_basename($modelName).'Factory';
            }

            // For package models
            return 'Ameax\\FieldkitCore\\Database\\Factories\\'.class_basename($modelName).'Factory';
        });

        // Run migrations for testing
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            FieldkitCoreServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Queue configuration for testing
        config()->set('queue.default', 'sync');

        // FieldKit configuration for testing
        config()->set('fieldkit.input_types', [
            'text' => \Ameax\FieldkitCore\Inputs\FieldKitTextInput::class,
            'email' => \Ameax\FieldkitCore\Inputs\FieldKitEmailInput::class,
            'number' => \Ameax\FieldkitCore\Inputs\FieldKitNumberInput::class,
            'textarea' => \Ameax\FieldkitCore\Inputs\FieldKitTextareaInput::class,
            'checkbox' => \Ameax\FieldkitCore\Inputs\FieldKitCheckboxInput::class,
            'select' => \Ameax\FieldkitCore\Inputs\FieldKitSelectInput::class,
            'radio' => \Ameax\FieldkitCore\Inputs\FieldKitRadioInput::class,
        ]);

        config()->set('fieldkit.definition_sources', [
            'config' => [
                'class' => \Ameax\FieldkitCore\DefinitionSources\ConfigDefinitionSource::class,
                'priority' => 200,
            ],
            'database' => [
                'class' => \Ameax\FieldkitCore\DefinitionSources\DatabaseDefinitionSource::class,
                'priority' => 100,
            ],
            'json' => [
                'class' => \Ameax\FieldkitCore\DefinitionSources\JsonDefinitionSource::class,
                'priority' => 50,
                'path' => storage_path('fieldkit'),
            ],
        ]);

        config()->set('fieldkit.handlers', []);
    }
}
