<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\DefinitionSources;

use Ameax\FieldkitCore\Contracts\FieldKitDefinitionSourceInterface;
use Ameax\FieldkitCore\Data\FieldKitFormData;

class ConfigDefinitionSource implements FieldKitDefinitionSourceInterface
{
    public function getFormDefinition(string $purposeToken): ?FieldKitFormData
    {
        $forms = config('fieldkit.forms', []);

        if (! isset($forms[$purposeToken])) {
            return null;
        }

        return FieldKitFormData::fromConfig($purposeToken, $forms[$purposeToken]);
    }

    public function getAvailablePurposes(): array
    {
        return array_keys(config('fieldkit.forms', []));
    }

    public function getPriority(): int
    {
        return 200; // Higher than database (Config First principle)
    }

    public function supports(string $purposeToken): bool
    {
        $forms = config('fieldkit.forms', []);

        return isset($forms[$purposeToken]);
    }
}
