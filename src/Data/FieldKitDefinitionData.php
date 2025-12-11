<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Data;

use Illuminate\Support\Collection;

class FieldKitDefinitionData
{
    public function __construct(
        public string $key,
        public string $type,
        public string $label,
        public ?string $description = null,
        public ?string $placeholder = null,
        public bool $isRequired = false,
        public array $validationRules = [],
        public int $sortOrder = 0,
        public bool $isActive = true,
        public ?array $conditions = null,
        public ?array $mappings = null,
        public Collection $options = new Collection,
    ) {}

    public static function fromModel(\Ameax\FieldkitCore\Models\FieldKitDefinition $definition): self
    {
        // Handle lazy loading disabled by checking if relationship is loaded
        $options = $definition->relationLoaded('options')
            ? $definition->options->map(function ($option) {
                /** @var \Ameax\FieldkitCore\Models\FieldKitOption $option */
                return FieldKitOptionData::fromModel($option);
            })
            : collect();

        // Get resolved validation rules (includes validation_pattern regex)
        $validationRules = $definition->getValidationRules();
        // Filter out 'required' as it's handled separately via isRequired
        $validationRules = array_values(array_filter($validationRules, fn ($rule) => $rule !== 'required'));

        return new self(
            key: $definition->key,
            type: $definition->type,
            label: $definition->label,
            description: $definition->description,
            placeholder: $definition->placeholder,
            isRequired: $definition->is_required,
            validationRules: $validationRules,
            sortOrder: $definition->sort_order,
            isActive: $definition->is_active,
            conditions: $definition->conditions,
            mappings: $definition->mappings,
            options: $options,
        );
    }

    public static function fromArray(array $data): self
    {
        $options = collect($data['options'] ?? [])->map(function ($optionData) {
            return FieldKitOptionData::fromArray($optionData);
        });

        // Handle validation_rules as string or array
        $validationRules = $data['validation_rules'] ?? [];
        if (is_string($validationRules) && ! empty($validationRules)) {
            $validationRules = explode('|', $validationRules);
        }

        return new self(
            key: $data['key'],
            type: $data['type'],
            label: $data['label'],
            description: $data['description'] ?? null,
            placeholder: $data['placeholder'] ?? null,
            isRequired: $data['is_required'] ?? false,
            validationRules: is_array($validationRules) ? $validationRules : [],
            sortOrder: $data['sort_order'] ?? 0,
            isActive: $data['is_active'] ?? true,
            conditions: $data['conditions'] ?? null,
            mappings: $data['mappings'] ?? null,
            options: $options,
        );
    }

    public function getOption(string $value): ?FieldKitOptionData
    {
        return $this->options->firstWhere('value', $value);
    }

    public function hasOptions(): bool
    {
        return $this->options->isNotEmpty();
    }

    public function getMappings(): Collection
    {
        return collect($this->mappings ?? []);
    }

    public function getMappingsForAdapter(string $adapter): Collection
    {
        return $this->getMappings()->filter(function ($mapping) use ($adapter) {
            return isset($mapping['adapter']) && $mapping['adapter'] === $adapter;
        });
    }

    /**
     * Check if this field should be displayed based on conditions
     *
     * @param  array  $formData  All form data (native + fieldkit)
     */
    public function shouldDisplay(array $formData = []): bool
    {
        if (empty($this->conditions)) {
            return true;  // No conditions = always show
        }

        // ALL conditions must be met (AND)
        foreach ($this->conditions as $condition) {
            $fieldKey = $condition['field_key'] ?? null;
            $expectedValues = $condition['answer_values'] ?? [];
            $operator = $condition['operator'] ?? 'in';

            // Dependent field not present in data
            if (! isset($formData[$fieldKey])) {
                return false;
            }

            $actualValue = $formData[$fieldKey];

            // Value normalization (bool → string)
            if (is_bool($actualValue)) {
                $actualValue = $actualValue ? 'true' : 'false';
            }

            switch ($operator) {
                case 'in':
                    if (! in_array($actualValue, $expectedValues, true)) {
                        return false;
                    }
                    break;

                case 'not_in':
                    if (in_array($actualValue, $expectedValues, true)) {
                        return false;
                    }
                    break;

                default:
                    return false;  // Unknown operator
            }
        }

        return true;  // All conditions met
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'label' => $this->label,
            'description' => $this->description,
            'placeholder' => $this->placeholder,
            'is_required' => $this->isRequired,
            'validation_rules' => $this->validationRules,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'conditions' => $this->conditions,
            'mappings' => $this->mappings,
            'options' => $this->options->map(fn ($option) => $option->toArray())->toArray(),
        ];
    }
}
