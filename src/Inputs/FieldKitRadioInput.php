<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Inputs;

class FieldKitRadioInput extends AbstractFieldKitInput
{
    public function getName(): string
    {
        return 'radio';
    }

    public function getLabel(): string
    {
        return 'Radio Buttons';
    }

    public function getDescription(): ?string
    {
        return 'Radio button group with optional descriptions';
    }

    public function getIcon(): ?string
    {
        return 'radio';
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
            'inline' => [
                'type' => 'checkbox',
                'label' => 'Inline Layout',
                'description' => 'Display radio buttons horizontally instead of vertically',
            ],
            'show_descriptions' => [
                'type' => 'checkbox',
                'label' => 'Show Descriptions',
                'description' => 'Display option descriptions below each radio button',
            ],
        ];
    }

    public function getDefaultValidationRules(): array
    {
        return ['string'];
    }
}
