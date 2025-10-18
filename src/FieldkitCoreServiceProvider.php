<?php

namespace Ameax\FieldkitCore;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Ameax\FieldkitCore\Commands\FieldkitCoreCommand;

class FieldkitCoreServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('fieldkit-core')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_fieldkit_core_table')
            ->hasCommand(FieldkitCoreCommand::class);
    }
}
