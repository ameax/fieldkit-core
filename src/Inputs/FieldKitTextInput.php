<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Inputs;

class FieldKitTextInput extends AbstractFieldKitInput
{
    public function getName(): string
    {
        return 'text';
    }

    public function getLabel(): string
    {
        return 'Text';
    }

    public function getDescription(): ?string
    {
        return 'Single-line text input';
    }

    public function getIcon(): ?string
    {
        return 'pencil';
    }

    public function getCompatibilityGroup(): string
    {
        return 'text';
    }

    public function getConfigurableAttributes(): array
    {
        return [
            'placeholder' => [
                'type' => 'text',
                'label' => 'Placeholder',
                'description' => 'Placeholder text shown when field is empty',
            ],
            'max_length' => [
                'type' => 'number',
                'label' => 'Maximum Length',
                'description' => 'Maximum number of characters allowed',
            ],
        ];
    }

    public function getDefaultValidationRules(): array
    {
        return ['string'];
    }
}
