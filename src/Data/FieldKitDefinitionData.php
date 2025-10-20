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
        public ?string $validationRules = null,
        public int $sortOrder = 0,
        public bool $isActive = true,
        public ?array $conditions = null,
        public ?array $mappings = null,
        public Collection $options = new Collection(),
    ) {}

    public static function fromModel(\Ameax\FieldkitCore\Models\FieldKitDefinition $definition): self
    {
        $options = $definition->options->map(function ($option) {
            return FieldKitOptionData::fromModel($option);
        });

        return new self(
            key: $definition->key,
            type: $definition->type,
            label: $definition->label,
            description: $definition->description,
            placeholder: $definition->placeholder,
            isRequired: $definition->is_required,
            validationRules: $definition->validation_rules,
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

        return new self(
            key: $data['key'],
            type: $data['type'],
            label: $data['label'],
            description: $data['description'] ?? null,
            placeholder: $data['placeholder'] ?? null,
            isRequired: $data['is_required'] ?? false,
            validationRules: $data['validation_rules'] ?? null,
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
            'options' => $this->options->map(fn($option) => $option->toArray())->toArray(),
        ];
    }
}