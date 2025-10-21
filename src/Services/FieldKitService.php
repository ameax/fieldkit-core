<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Services;

use Ameax\FieldkitCore\Contracts\FieldKitAdapterInterface;
use Ameax\FieldkitCore\Data\FieldKitFormData;
use Ameax\FieldkitCore\FieldKitInputRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FieldKitService
{
    public function __construct(
        protected FieldKitInputRegistry $registry,
        protected FieldKitDefinitionResolver $resolver
    ) {}

    /**
     * Get all active fields for a purpose token
     */
    public function getFieldsForPurpose(string $purposeToken): Collection
    {
        $formDefinition = $this->resolver->resolve($purposeToken);

        if (! $formDefinition) {
            return new Collection;
        }

        return $formDefinition->fields->filter(fn ($field) => $field->isActive);
    }

    /**
     * Render form components using the specified adapter
     */
    public function renderFormComponents(
        string $purposeToken,
        FieldKitAdapterInterface $adapter,
        array $formData = []
    ): array {
        $fields = $this->getFieldsForPurpose($purposeToken);
        $components = [];

        foreach ($fields as $field) {
            // Skip if field conditions are not met
            if (! $this->evaluateConditions($field->conditions ?? [], $formData)) {
                continue;
            }

            // Skip if adapter doesn't support this input type
            if (! $adapter->supports($field->type)) {
                continue;
            }

            // Get input type from registry
            if (! $this->registry->has($field->type)) {
                continue;
            }

            $input = $this->registry->resolve($field->type);

            // Build configuration array
            $config = [
                'name' => $field->key,
                'label' => $field->label,
                'placeholder' => $field->placeholder,
                'description' => $field->description,
                'required' => $field->isRequired,
                'validation' => $field->validationRules,
                'sort_order' => $field->sortOrder,
                'conditions' => $field->conditions ?? [],
                'all_fields' => $fields->map(function ($f) {
                    return [
                        'key' => $f->key,
                        'conditions' => $f->conditions ?? [],
                    ];
                })->toArray(),
            ];

            // Add options for inputs that support them
            if ($input->supportsOptions() && $field->hasOptions()) {
                $config['options'] = $field->options->pluck('label', 'value')->toArray();
            }

            // Render component using adapter
            $component = $input->render($adapter, $config);
            if ($component) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * Store field values to model and execute handlers
     */
    public function storeFieldValues(
        string $purposeToken,
        array $formData,
        Model $model,
        string $jsonColumn = 'fieldkit_data'
    ): void {
        // Load form definition
        $formDefinition = $this->resolver->resolve($purposeToken);

        if (! $formDefinition) {
            throw new \Exception("No form definition found for purpose: {$purposeToken}");
        }

        // Filter form data to only include defined fields
        $fieldKeys = $formDefinition->fields->pluck('key')->toArray();
        $filteredData = array_intersect_key($formData, array_flip($fieldKeys));

        // Transform values according to input types
        $transformedData = $this->transformValues($formDefinition, $filteredData);

        // 1. Always store in JSON column
        if (! empty($transformedData)) {
            $existingData = $model->{$jsonColumn} ?? [];
            $mergedData = array_merge($existingData, $transformedData);
            $model->{$jsonColumn} = $mergedData;
            $model->save();
        }

        // 2. Execute handlers if configured
        $this->executeHandlers($formDefinition, $model, $filteredData);
    }

    /**
     * Transform values according to input type transformations
     */
    protected function transformValues(FieldKitFormData $formDefinition, array $formData): array
    {
        $transformed = [];

        foreach ($formDefinition->fields as $field) {
            if (! isset($formData[$field->key])) {
                continue;
            }

            if ($this->registry->has($field->type)) {
                $input = $this->registry->resolve($field->type);
                $transformed[$field->key] = $input->transformValue($formData[$field->key], 'store');
            } else {
                $transformed[$field->key] = $formData[$field->key];
            }
        }

        return $transformed;
    }

    /**
     * Execute configured handlers for external system integration
     */
    protected function executeHandlers(FieldKitFormData $formDefinition, Model $model, array $formData): void
    {
        if (empty($formDefinition->handlers)) {
            return;
        }

        // Extract mappings from all fields
        $mappings = $this->extractMappings($formDefinition, $formData);

        if ($mappings->isEmpty()) {
            return;
        }

        foreach ($formDefinition->handlers as $handlerClass) {
            if (! class_exists($handlerClass)) {
                continue;
            }

            $handler = app($handlerClass);

            // Filter mappings that this handler supports
            $relevantMappings = $mappings->filter(
                fn ($mapping) => $handler->supports($mapping['adapter'])
            );

            if ($relevantMappings->isEmpty()) {
                continue;
            }

            // Execute sync or async
            if ($handler->shouldQueue()) {
                // Dispatch to queue
                dispatch(new \Ameax\FieldkitCore\Jobs\ProcessFieldKitMapping(
                    $handlerClass,
                    $model,
                    $relevantMappings,
                    $formData
                ));
            } else {
                // Execute immediately
                $handler->handle($model, $relevantMappings, $formData);
            }
        }
    }

    /**
     * Extract mappings from form definition
     */
    protected function extractMappings(FieldKitFormData $formDefinition, array $formData): Collection
    {
        $mappings = [];

        foreach ($formDefinition->fields as $field) {
            if (! isset($formData[$field->key]) || empty($field->mappings)) {
                continue;
            }

            foreach ($field->mappings as $mapping) {
                $mappings[] = [
                    'key' => $field->key,
                    'field_value' => $formData[$field->key],
                    'adapter' => $mapping['adapter'],
                    'target' => $mapping['target'],
                    'config' => $mapping['config'] ?? [],
                    'transformations' => $mapping['transformations'] ?? null,
                    'conditions' => $mapping['conditions'] ?? null,
                ];
            }
        }

        return collect($mappings);
    }

    /**
     * Evaluate field conditions against form data
     */
    protected function evaluateConditions(array $conditions, array $formData): bool
    {
        // If no conditions, field is always shown
        if (empty($conditions)) {
            return true;
        }

        // All conditions must be satisfied (AND logic)
        foreach ($conditions as $condition) {
            if (! $this->evaluateCondition($condition, $formData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a single condition
     */
    protected function evaluateCondition(array $condition, array $formData): bool
    {
        $fieldKey = $condition['field_key'] ?? null;
        $operator = $condition['operator'] ?? 'equals';
        $answerValues = $condition['answer_values'] ?? [];

        if (! $fieldKey) {
            return false;
        }

        $fieldValue = $formData[$fieldKey] ?? null;

        return match ($operator) {
            'equals' => in_array($fieldValue, $answerValues),
            'not_equals' => ! in_array($fieldValue, $answerValues),
            'contains' => is_string($fieldValue) && str_contains($fieldValue, $answerValues[0] ?? ''),
            'empty' => empty($fieldValue),
            'not_empty' => ! empty($fieldValue),
            default => false,
        };
    }
}
