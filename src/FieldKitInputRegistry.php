<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore;

use Ameax\FieldkitCore\Contracts\FieldKitInputInterface;
use InvalidArgumentException;

class FieldKitInputRegistry
{
    private array $inputs = [];

    /**
     * Registers an input type
     */
    public function register(string $key, string $class): void
    {
        if (!is_subclass_of($class, FieldKitInputInterface::class)) {
            throw new InvalidArgumentException(
                "{$class} must implement FieldKitInputInterface"
            );
        }

        $this->inputs[$key] = $class;
    }

    /**
     * Gets all registered input types
     */
    public function all(): array
    {
        return $this->inputs;
    }

    /**
     * Resolves an input type
     */
    public function resolve(string $key): FieldKitInputInterface
    {
        if (!isset($this->inputs[$key])) {
            throw new InvalidArgumentException("Input type '{$key}' is not registered");
        }

        $class = $this->inputs[$key];
        
        return new $class();
    }

    /**
     * Gets options for admin dropdown (dynamic!)
     */
    public function getOptionsForAdmin(): array
    {
        $options = [];

        foreach ($this->inputs as $key => $class) {
            $instance = $this->resolve($key);
            $options[$key] = $instance->getLabel();
        }

        return $options;
    }

    /**
     * Check if an input type is registered
     */
    public function has(string $key): bool
    {
        return isset($this->inputs[$key]);
    }

    /**
     * Get all input types that support options
     */
    public function getTypesWithOptions(): array
    {
        $types = [];

        foreach ($this->inputs as $key => $class) {
            $instance = $this->resolve($key);
            if ($instance->supportsOptions()) {
                $types[$key] = $instance->getLabel();
            }
        }

        return $types;
    }
}