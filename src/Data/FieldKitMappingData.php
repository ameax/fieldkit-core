<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Data;

class FieldKitMappingData
{
    public function __construct(
        public string $adapter,
        public string $target,
        public ?array $transformations = null,
        public ?array $conditions = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            adapter: $data['adapter'],
            target: $data['target'],
            transformations: $data['transformations'] ?? null,
            conditions: $data['conditions'] ?? null,
        );
    }

    /**
     * Get the table name from dot notation (e.g., "customer.xcu_newsletter" -> "customer")
     */
    public function getTable(): string
    {
        return explode('.', $this->target)[0];
    }

    /**
     * Get the column name from dot notation (e.g., "customer.xcu_newsletter" -> "xcu_newsletter")
     */
    public function getColumn(): string
    {
        $parts = explode('.', $this->target);

        return $parts[1] ?? $parts[0];
    }

    /**
     * Check if this mapping has conditions that need to be evaluated
     */
    public function hasConditions(): bool
    {
        return ! empty($this->conditions);
    }

    /**
     * Check if this mapping has transformations
     */
    public function hasTransformations(): bool
    {
        return ! empty($this->transformations);
    }

    public function toArray(): array
    {
        return [
            'adapter' => $this->adapter,
            'target' => $this->target,
            'transformations' => $this->transformations,
            'conditions' => $this->conditions,
        ];
    }
}
