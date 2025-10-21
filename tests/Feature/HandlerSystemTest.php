<?php

declare(strict_types=1);

use Ameax\FieldkitCore\Jobs\ProcessFieldKitMapping;
use Ameax\FieldkitCore\Contracts\FieldKitMappingHandlerInterface;
use Illuminate\Support\Facades\Queue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

beforeEach(function () {
    Queue::fake();
});

it('processes mapping with sync handler', function () {
    $handler = new class implements FieldKitMappingHandlerInterface {
        public static $lastProcessedData = null;
        
        public function handle(string $target, mixed $value, array $config = []): void
        {
            static::$lastProcessedData = [
                'target' => $target,
                'value' => $value,
                'config' => $config
            ];
        }
        
        public function isAsync(): bool
        {
            return false;
        }
    };
    
    // Register the test handler
    config(['fieldkit.handlers.test_sync' => get_class($handler)]);
    
    $job = new ProcessFieldKitMapping(
        'test_sync',
        'customer.phone',
        '+49 123 456789',
        ['format' => 'international']
    );
    
    $job->handle();
    
    expect($handler::$lastProcessedData)->toBe([
        'target' => 'customer.phone',
        'value' => '+49 123 456789',
        'config' => ['format' => 'international']
    ]);
});

it('processes mapping with async handler', function () {
    $handler = new class implements FieldKitMappingHandlerInterface {
        public static $lastProcessedData = null;
        
        public function supports(string $adapter): bool
        {
            return $adapter === 'test_adapter';
        }
        
        public function handle(Model $model, Collection $mappings, array $formData): void
        {
            static::$lastProcessedData = [
                'model' => $model,
                'mappings' => $mappings->toArray(),
                'formData' => $formData
            ];
        }
        
        public function shouldQueue(): bool
        {
            return true;
        }
    };
    
    // Register the test handler
    config(['fieldkit.handlers.test_async' => get_class($handler)]);
    
    $job = new ProcessFieldKitMapping(
        'test_async',
        'customer.email',
        'test@example.com',
        ['list_id' => '123']
    );
    
    $job->handle();
    
    expect($handler::$lastProcessedData)->toBe([
        'target' => 'customer.email',
        'value' => 'test@example.com',
        'config' => ['list_id' => '123']
    ]);
});

it('throws exception for unknown handler', function () {
    $job = new ProcessFieldKitMapping(
        'unknown_handler',
        'target',
        'value',
        []
    );
    
    $job->handle();
})->throws(InvalidArgumentException::class, 'Handler unknown_handler not found in configuration');

it('throws exception for invalid handler class', function () {
    config(['fieldkit.handlers.invalid' => 'NonExistentClass']);
    
    $job = new ProcessFieldKitMapping(
        'invalid',
        'target',
        'value',
        []
    );
    
    $job->handle();
})->throws(InvalidArgumentException::class, 'Handler class NonExistentClass does not exist');

it('throws exception for handler not implementing interface', function () {
    config(['fieldkit.handlers.invalid_interface' => stdClass::class]);
    
    $job = new ProcessFieldKitMapping(
        'invalid_interface',
        'target',
        'value',
        []
    );
    
    $job->handle();
})->throws(InvalidArgumentException::class, 'Handler stdClass must implement FieldKitMappingHandlerInterface');