<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Data;

class FieldKitOptionData
{
    public function __construct(
        public string $value,
        public string $label,
        public ?string $description = null,
        public ?string $icon = null,
        public ?string $externalIdentifier = null,
        public int $sortOrder = 0,
    ) {}

    public static function fromModel(\Ameax\FieldkitCore\Models\FieldKitOption $option): self
    {
        return new self(
            value: $option->value,
            label: $option->label,
            description: $option->description,
            icon: $option->icon,
            externalIdentifier: $option->external_identifier,
            sortOrder: $option->sort_order,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'],
            label: $data['label'],
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
            externalIdentifier: $data['external_identifier'] ?? null,
            sortOrder: $data['sort_order'] ?? 0,
        );
    }

    /**
     * Returns the external ID (fallback to value)
     */
    public function getExternalIdentifier(): string
    {
        return $this->externalIdentifier ?? $this->value;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'description' => $this->description,
            'icon' => $this->icon,
            'external_identifier' => $this->externalIdentifier,
            'sort_order' => $this->sortOrder,
        ];
    }
}
