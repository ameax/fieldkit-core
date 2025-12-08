<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Contracts;

/**
 * Provides context configuration UI for admin panel
 *
 * Apps implement this to customize which context fields
 * appear in the FieldKit admin form editor.
 */
interface ContextProviderInterface
{
    /**
     * Returns Filament form fields for configuring context
     *
     * These fields will be displayed in the "Context" section
     * of the FieldKit form editor and stored in context_data.
     *
     * @return array<mixed> Filament form components
     */
    public function getFormFields(): array;

    /**
     * Returns Filament table columns for displaying context
     *
     * These columns will be displayed in the form list view
     * to show which context rules apply to each form.
     *
     * @return array<mixed> Filament table columns
     */
    public function getTableColumns(): array;

    /**
     * Returns the section label for the context UI
     */
    public function getSectionLabel(): string;

    /**
     * Returns the section description for the context UI
     */
    public function getSectionDescription(): ?string;
}
