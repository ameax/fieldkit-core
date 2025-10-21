<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Inputs;

class FieldKitEmailInput extends AbstractFieldKitInput
{
    public function getName(): string
    {
        return 'email';
    }

    public function getLabel(): string
    {
        return 'Email';
    }

    public function getDescription(): ?string
    {
        return 'Email input with browser validation';
    }

    public function getIcon(): ?string
    {
        return 'at-symbol';
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
                'description' => 'Placeholder text shown when field is empty (e.g., user@example.com)',
            ],
        ];
    }

    public function getDefaultValidationRules(): array
    {
        return ['email'];
    }
}
