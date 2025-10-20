<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Handlers;

use Ameax\FieldkitCore\Contracts\FieldKitMappingHandlerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AmeaxCustomerHandler implements FieldKitMappingHandlerInterface
{
    /**
     * Can this handler process the mappings?
     */
    public function supports(string $adapter): bool
    {
        return $adapter === 'ameax_column';
    }

    /**
     * Process the mapped data
     */
    public function handle(Model $model, Collection $mappings, array $formData): void
    {
        foreach ($mappings as $mapping) {
            $this->processMapping($model, $mapping, $formData);
        }
    }

    /**
     * Should this handler be executed asynchronously (Queue)?
     */
    public function shouldQueue(): bool
    {
        return false; // Execute immediately for Ameax column updates
    }

    /**
     * Process individual mapping
     */
    protected function processMapping(Model $model, array $mapping, array $formData): void
    {
        $target = $mapping['target'];
        $fieldKey = $mapping['field_key'] ?? null;
        
        if (!$fieldKey || !isset($formData[$fieldKey])) {
            return;
        }

        $value = $formData[$fieldKey];
        
        // Transform value based on configuration
        $transformedValue = $this->transformValue($value, $mapping['config'] ?? []);
        
        // Apply dot notation to model
        $this->setNestedAttribute($model, $target, $transformedValue);
        
        // Save the model
        $model->save();
    }

    /**
     * Transform value according to mapping configuration
     */
    protected function transformValue(mixed $value, array $config): mixed
    {
        // Boolean transformation for checkboxes
        if (isset($config['type']) && $config['type'] === 'boolean') {
            return $value ? 1 : 0;
        }
        
        // String transformation
        if (isset($config['prefix'])) {
            return $config['prefix'] . $value;
        }
        
        if (isset($config['suffix'])) {
            return $value . $config['suffix'];
        }
        
        // Default: return as-is
        return $value;
    }

    /**
     * Set nested attribute using dot notation
     */
    protected function setNestedAttribute(Model $model, string $target, mixed $value): void
    {
        $parts = explode('.', $target);
        
        if (count($parts) === 1) {
            // Direct attribute
            $model->{$parts[0]} = $value;
            return;
        }
        
        if (count($parts) === 2 && $parts[0] === 'customer') {
            // Customer relationship or direct attribute
            if ($model->hasAttribute($parts[1])) {
                $model->{$parts[1]} = $value;
            } elseif (method_exists($model, 'customer') && $model->customer) {
                $model->customer->{$parts[1]} = $value;
                $model->customer->save();
            }
            return;
        }
        
        // Complex nested relationships
        $current = $model;
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $relation = $parts[$i];
            if (method_exists($current, $relation)) {
                $current = $current->{$relation};
                if (!$current) {
                    return; // Relationship doesn't exist
                }
            }
        }
        
        $finalAttribute = end($parts);
        $current->{$finalAttribute} = $value;
        $current->save();
    }
}