<?php

declare(strict_types=1);

use Ameax\FieldkitCore\Contracts\FieldKitMappingHandlerInterface;
use Ameax\FieldkitCore\Jobs\ProcessFieldKitMapping;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

it('processes mapping with sync handler', function () {
    $handler = new class implements FieldKitMappingHandlerInterface
    {
        public static $lastProcessedData = null;

        public function supports(string $adapter): bool
        {
            return $adapter === 'test_adapter';
        }

        public function handle(Model $model, Collection $mappings, array $formData): void
        {
            self::$lastProcessedData = [
                'model' => $model,
                'mappings' => $mappings->toArray(),
                'formData' => $formData,
            ];
        }

        public function shouldQueue(): bool
        {
            return false;
        }
    };

    // Create a test model
    $model = new class extends Model
    {
        protected $table = 'test_models';

        public $timestamps = false;
    };

    // Create test mappings
    $mappings = collect([
        [
            'field_key' => 'phone',
            'field_value' => '+49 123 456789',
            'adapter' => 'test_adapter',
            'target' => 'customer.phone',
            'transformations' => null,
            'conditions' => null,
        ],
    ]);

    $formData = ['phone' => '+49 123 456789'];

    $job = new ProcessFieldKitMapping(
        get_class($handler),
        $model,
        $mappings,
        $formData
    );

    $job->handle();

    expect($handler::$lastProcessedData)->toBe([
        'model' => $model,
        'mappings' => $mappings->toArray(),
        'formData' => $formData,
    ]);
});

it('processes mapping with async handler', function () {
    $handler = new class implements FieldKitMappingHandlerInterface
    {
        public static $lastProcessedData = null;

        public function supports(string $adapter): bool
        {
            return $adapter === 'test_adapter';
        }

        public function handle(Model $model, Collection $mappings, array $formData): void
        {
            self::$lastProcessedData = [
                'model' => $model,
                'mappings' => $mappings->toArray(),
                'formData' => $formData,
            ];
        }

        public function shouldQueue(): bool
        {
            return true;
        }
    };

    // Create a test model
    $model = new class extends Model
    {
        protected $table = 'test_models';

        public $timestamps = false;
    };

    // Create test mappings
    $mappings = collect([
        [
            'field_key' => 'email',
            'field_value' => 'test@example.com',
            'adapter' => 'test_adapter',
            'target' => 'customer.email',
            'transformations' => null,
            'conditions' => null,
        ],
    ]);

    $formData = ['email' => 'test@example.com'];

    $job = new ProcessFieldKitMapping(
        get_class($handler),
        $model,
        $mappings,
        $formData
    );

    $job->handle();

    expect($handler::$lastProcessedData)->toBe([
        'model' => $model,
        'mappings' => $mappings->toArray(),
        'formData' => $formData,
    ]);
});

it('throws exception for unknown handler', function () {
    $model = new class extends Model
    {
        protected $table = 'test_models';

        public $timestamps = false;
    };

    $job = new ProcessFieldKitMapping(
        'NonExistentHandlerClass',
        $model,
        collect([]),
        []
    );

    $job->handle();
})->throws(\Exception::class, 'Handler class not found: NonExistentHandlerClass');

it('throws exception for handler not implementing interface', function () {
    $model = new class extends Model
    {
        protected $table = 'test_models';

        public $timestamps = false;
    };

    $job = new ProcessFieldKitMapping(
        stdClass::class,
        $model,
        collect([]),
        []
    );

    $job->handle();
})->throws(\Exception::class, 'Handler must implement FieldKitMappingHandlerInterface: stdClass');
