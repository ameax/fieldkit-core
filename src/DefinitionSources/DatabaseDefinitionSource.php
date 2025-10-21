<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\DefinitionSources;

use Ameax\FieldkitCore\Contracts\FieldKitDefinitionSourceInterface;
use Ameax\FieldkitCore\Data\FieldKitFormData;
use Ameax\FieldkitCore\Models\FieldKitForm;

class DatabaseDefinitionSource implements FieldKitDefinitionSourceInterface
{
    public function getFormDefinition(string $purposeToken): ?FieldKitFormData
    {
        $form = FieldKitForm::byPurpose($purposeToken)
            ->active()
            ->with(['fields.options'])
            ->first();

        if (! $form) {
            return null;
        }

        return FieldKitFormData::fromModel($form);
    }

    public function getAvailablePurposes(): array
    {
        return FieldKitForm::active()
            ->pluck('purpose_token')
            ->toArray();
    }

    public function getPriority(): int
    {
        return 100; // Highest priority
    }

    public function supports(string $purposeToken): bool
    {
        return FieldKitForm::byPurpose($purposeToken)->active()->exists();
    }
}
