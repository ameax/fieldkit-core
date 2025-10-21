<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Inputs;

use Ameax\FieldkitCore\Contracts\FieldKitAdapterInterface;
use Ameax\FieldkitCore\Contracts\FieldKitInputInterface;
use Ameax\FieldkitCore\FieldKitInputRegistry;

abstract class AbstractFieldKitInput implements FieldKitInputInterface
{
    // Must be implemented by concrete classes
    abstract public function getName(): string;

    abstract public function getLabel(): string;

    abstract public function getCompatibilityGroup(): string;

    // Default implementations (can be overridden)
    public function getDescription(): ?string
    {
        return null;
    }

    public function getIcon(): ?string
    {
        return null;
    }

    public function getDefaultValidationRules(): array
    {
        return [];
    }

    public function supportsOptions(): bool
    {
        return false;
    }

    public function supportsMultiple(): bool
    {
        return false;
    }

    public function isTableCompatible(): bool
    {
        return true;
    }

    public function getConfigurableAttributes(): array
    {
        return [];
    }

    public function transformValue(mixed $value, string $direction = 'store'): mixed
    {
        return $value;
    }

    public function render(FieldKitAdapterInterface $adapter, array $config): mixed
    {
        return $adapter->createComponent($this->getName(), $config);
    }

    /**
     * Can this input type be migrated from another type?
     * Types in the same compatibility group can be migrated between each other
     */
    public function canMigrateFrom(string $fromType): bool
    {
        $registry = app(FieldKitInputRegistry::class);

        if (! $registry->has($fromType)) {
            return false;
        }

        $fromInput = $registry->resolve($fromType);

        return $fromInput->getCompatibilityGroup() === $this->getCompatibilityGroup();
    }
}
