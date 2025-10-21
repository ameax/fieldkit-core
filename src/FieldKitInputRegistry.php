<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore;

use Ameax\FieldkitCore\Contracts\FieldKitInputInterface;
use InvalidArgumentException;

class FieldKitInputRegistry
{
    private array $inputs = [];

    public function __construct(?array $inputTypes = null)
    {
        if ($inputTypes !== null) {
            foreach ($inputTypes as $key => $class) {
                $this->register($key, $class);
            }
        } else {
            $this->loadFromConfig();
        }
    }

    /**
     * Load input types from configuration
     */
    private function loadFromConfig(): void
    {
        if (function_exists('config')) {
            $configTypes = config('fieldkit.input_types', []);

            foreach ($configTypes as $key => $class) {
                $this->register($key, $class);
            }
        }
    }

    /**
     * Registers an input type
     */
    public function register(string $key, string $class): void
    {
        if (! is_subclass_of($class, FieldKitInputInterface::class)) {
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
     * Gets an input class by type
     */
    public function get(string $key): string
    {
        if (! isset($this->inputs[$key])) {
            throw new InvalidArgumentException("Input type {$key} not found in registry");
        }

        return $this->inputs[$key];
    }

    /**
     * Resolves an input type
     */
    public function resolve(string $key): FieldKitInputInterface
    {
        if (! isset($this->inputs[$key])) {
            throw new InvalidArgumentException("Input type '{$key}' is not registered");
        }

        $class = $this->inputs[$key];

        return new $class;
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
