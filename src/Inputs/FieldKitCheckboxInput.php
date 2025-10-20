<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Inputs;

class FieldKitCheckboxInput extends AbstractFieldKitInput
{
    public function getName(): string
    {
        return 'checkbox';
    }

    public function getLabel(): string
    {
        return 'Checkbox';
    }

    public function getDescription(): ?string
    {
        return 'Boolean checkbox for consent or yes/no questions';
    }

    public function getIcon(): ?string
    {
        return 'check-square';
    }

    public function getCompatibilityGroup(): string
    {
        return 'boolean';
    }

    public function getConfigurableAttributes(): array
    {
        return [
            'checked_value' => [
                'type' => 'text',
                'label' => 'Checked Value',
                'description' => 'Value to store when checkbox is checked (default: true)',
            ],
            'unchecked_value' => [
                'type' => 'text',
                'label' => 'Unchecked Value',
                'description' => 'Value to store when checkbox is unchecked (default: false)',
            ],
        ];
    }

    public function getDefaultValidationRules(): array
    {
        return ['boolean'];
    }

    public function transformValue(mixed $value, string $direction = 'store'): mixed
    {
        if ($direction === 'store') {
            // Convert various truthy/falsy values to boolean
            return (bool) $value;
        }

        return $value;
    }
}