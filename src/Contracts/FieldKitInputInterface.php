<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Contracts;

interface FieldKitInputInterface
{
    /**
     * Internal name of the input type (e.g. 'text', 'email')
     */
    public function getName(): string;

    /**
     * Translated label for admin UI
     */
    public function getLabel(): string;

    /**
     * Optional description for admin UI
     */
    public function getDescription(): ?string;

    /**
     * Icon for admin UI (Heroicon name)
     */
    public function getIcon(): ?string;

    /**
     * Renders the input field with the given adapter
     */
    public function render(FieldKitAdapterInterface $adapter, array $config): mixed;

    /**
     * Default validation rules for this type
     */
    public function getDefaultValidationRules(): array;

    /**
     * Does this type support options? (Select, Radio)
     */
    public function supportsOptions(): bool;

    /**
     * Supports multiple selection? (Multi-Select)
     */
    public function supportsMultiple(): bool;

    /**
     * Can be displayed in Filament table?
     */
    public function isTableCompatible(): bool;

    /**
     * Configurable attributes for admin UI
     *
     * @return array ['placeholder' => ['type' => 'text', 'label' => 'Placeholder']]
     */
    public function getConfigurableAttributes(): array;

    /**
     * Compatibility group for type switching
     * Fields with the same group can be switched between each other
     *
     * @return string 'text', 'number', 'boolean', 'single_choice'
     */
    public function getCompatibilityGroup(): string;

    /**
     * Transforms value for storage/display
     *
     * @param  mixed  $value  Value
     * @param  string  $direction  'store' or 'retrieve'
     */
    public function transformValue(mixed $value, string $direction = 'store'): mixed;
}
