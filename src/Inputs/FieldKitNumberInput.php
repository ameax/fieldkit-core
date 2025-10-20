<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Inputs;

class FieldKitNumberInput extends AbstractFieldKitInput
{
    public function getName(): string
    {
        return 'number';
    }

    public function getLabel(): string
    {
        return 'Number';
    }

    public function getDescription(): ?string
    {
        return 'Numeric input with optional min/max values';
    }

    public function getIcon(): ?string
    {
        return 'hashtag';
    }

    public function getCompatibilityGroup(): string
    {
        return 'number';
    }

    public function getConfigurableAttributes(): array
    {
        return [
            'placeholder' => [
                'type' => 'text',
                'label' => 'Placeholder',
                'description' => 'Placeholder text shown when field is empty',
            ],
            'min' => [
                'type' => 'number',
                'label' => 'Minimum Value',
                'description' => 'Minimum allowed value',
            ],
            'max' => [
                'type' => 'number',
                'label' => 'Maximum Value',
                'description' => 'Maximum allowed value',
            ],
            'step' => [
                'type' => 'number',
                'label' => 'Step',
                'description' => 'Increment/decrement step (e.g., 0.01 for currency)',
            ],
        ];
    }

    public function getDefaultValidationRules(): array
    {
        return ['numeric'];
    }

    public function transformValue(mixed $value, string $direction = 'store'): mixed
    {
        if ($direction === 'store' && $value !== null && $value !== '') {
            return (float) $value;
        }

        return $value;
    }
}