<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Contracts;

interface FieldKitAdapterInterface
{
    /**
     * Creates a framework-specific form component
     *
     * @param string $type Input type (text, select, checkbox, etc.)
     * @param array $config Configuration
     * @return mixed Framework-specific component
     */
    public function createComponent(string $type, array $config): mixed;

    /**
     * Does this adapter support the given type?
     */
    public function supports(string $type): bool;

    /**
     * Name of the adapter (e.g. 'filament', 'livewire')
     */
    public function getName(): string;
}