<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Inputs;

class FieldKitSelectInput extends AbstractFieldKitInput
{
    public function getName(): string
    {
        return 'select';
    }

    public function getLabel(): string
    {
        return 'Select (Dropdown)';
    }

    public function getDescription(): ?string
    {
        return 'Dropdown selection with predefined options';
    }

    public function getIcon(): ?string
    {
        return 'chevron-down';
    }

    public function getCompatibilityGroup(): string
    {
        return 'single_choice';
    }

    public function supportsOptions(): bool
    {
        return true;
    }

    public function supportsMultiple(): bool
    {
        return false;
    }

    public function getConfigurableAttributes(): array
    {
        return [
            'placeholder' => [
                'type' => 'text',
                'label' => 'Placeholder',
                'description' => 'Text shown when no option is selected',
            ],
            'searchable' => [
                'type' => 'checkbox',
                'label' => 'Searchable',
                'description' => 'Allow users to search through options',
            ],
        ];
    }

    public function getDefaultValidationRules(): array
    {
        return ['string'];
    }
}
