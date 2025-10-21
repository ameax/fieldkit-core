<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Inputs;

class FieldKitTextareaInput extends AbstractFieldKitInput
{
    public function getName(): string
    {
        return 'textarea';
    }

    public function getLabel(): string
    {
        return 'Textarea';
    }

    public function getDescription(): ?string
    {
        return 'Multi-line text input';
    }

    public function getIcon(): ?string
    {
        return 'document-text';
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
            'rows' => [
                'type' => 'number',
                'label' => 'Rows',
                'description' => 'Number of visible text rows',
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
