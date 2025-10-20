<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Contracts;

use Ameax\FieldkitCore\Data\FieldKitFormData;

interface FieldKitDefinitionSourceInterface
{
    /**
     * Loads form definition for a purpose
     */
    public function getFormDefinition(string $purposeToken): ?FieldKitFormData;

    /**
     * All available purpose tokens
     */
    public function getAvailablePurposes(): array;

    /**
     * Priority of this source (higher = preferred)
     */
    public function getPriority(): int;

    /**
     * Can this source provide the purpose?
     */
    public function supports(string $purposeToken): bool;
}