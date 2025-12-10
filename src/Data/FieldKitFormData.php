<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Data;

use Ameax\FieldkitCore\Contracts\ContextResolverInterface;
use Illuminate\Support\Collection;

class FieldKitFormData
{
    public function __construct(
        public string $purposeToken,
        public string $name,
        public ?string $description = null,
        public bool $isActive = true,
        public ?string $ownerType = null,
        public ?int $ownerId = null,
        public Collection $fields = new Collection,
        public array $handlers = [],
    ) {}

    public static function fromModel(\Ameax\FieldkitCore\Models\FieldKitForm $form): self
    {
        // Use already-loaded fields relationship and filter for active fields
        // to avoid lazy loading when fields.options is already eager loaded
        $activeFields = $form->relationLoaded('fields')
            ? $form->fields->where('is_active', true)
            : $form->activeFields;

        // Apply field-level context filtering if enabled
        $fieldResolver = static::getFieldContextResolver();
        if ($fieldResolver) {
            $activeFields = $activeFields->filter(
                fn ($field) => $fieldResolver->matchesContext($field->context_data)
            );
        }

        $fields = $activeFields->map(function ($field) {
            /** @var \Ameax\FieldkitCore\Models\FieldKitDefinition $field */
            return FieldKitDefinitionData::fromModel($field);
        });

        return new self(
            purposeToken: $form->purpose_token,
            name: $form->name,
            description: $form->description,
            isActive: $form->is_active,
            ownerType: $form->owner_type,
            ownerId: $form->owner_id,
            fields: $fields,
        );
    }

    /**
     * Get field-level context resolver if enabled
     */
    protected static function getFieldContextResolver(): ?ContextResolverInterface
    {
        if (! config('fieldkit.context.enabled', false)) {
            return null;
        }

        $resolverConfig = config('fieldkit.context.field_resolver') ?? config('fieldkit.context.resolver');

        if (! $resolverConfig) {
            return null;
        }

        // Handle closure (factory method) or class string
        if ($resolverConfig instanceof \Closure) {
            return $resolverConfig();
        }

        if (is_string($resolverConfig) && class_exists($resolverConfig)) {
            return app($resolverConfig);
        }

        return null;
    }

    public static function fromConfig(string $purposeToken, array $config): self
    {
        $fields = collect($config['fields'] ?? [])->map(function ($fieldConfig) {
            return FieldKitDefinitionData::fromArray($fieldConfig);
        });

        return new self(
            purposeToken: $purposeToken,
            name: $config['name'] ?? $purposeToken,
            description: $config['description'] ?? null,
            isActive: $config['is_active'] ?? true,
            fields: $fields,
            handlers: $config['handlers'] ?? [],
        );
    }

    public function getField(string $key): ?FieldKitDefinitionData
    {
        return $this->fields->firstWhere('key', $key);
    }

    public function hasField(string $key): bool
    {
        return $this->fields->contains('key', $key);
    }

    public function toArray(): array
    {
        return [
            'purpose_token' => $this->purposeToken,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'owner_type' => $this->ownerType,
            'owner_id' => $this->ownerId,
            'fields' => $this->fields->map(fn ($field) => $field->toArray())->toArray(),
            'handlers' => $this->handlers,
        ];
    }
}
