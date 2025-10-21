<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Handlers;

use Ameax\FieldkitCore\Contracts\FieldKitMappingHandlerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Example handler that logs field mappings
 * This is a reference implementation showing how to create custom handlers
 */
class ExampleLoggingHandler implements FieldKitMappingHandlerInterface
{
    public function supports(string $adapter): bool
    {
        return $adapter === 'example_logging';
    }

    public function handle(Model $model, Collection $mappings, array $formData): void
    {
        Log::info('FieldKit mapping processing', [
            'handler' => static::class,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'mappings_count' => $mappings->count(),
            'form_data_keys' => array_keys($formData),
            'mappings' => $mappings->toArray(),
        ]);

        // Example: Process each mapping
        foreach ($mappings as $mapping) {
            Log::debug('Processing FieldKit mapping', [
                'field_key' => $mapping['field_key'],
                'field_value' => $mapping['field_value'],
                'target' => $mapping['target'],
                'adapter' => $mapping['adapter'],
            ]);
        }
    }

    public function shouldQueue(): bool
    {
        // This example handler can run synchronously since it's just logging
        return false;
    }
}
