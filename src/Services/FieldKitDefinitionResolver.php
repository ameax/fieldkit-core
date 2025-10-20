<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Services;

use Ameax\FieldkitCore\Contracts\FieldKitDefinitionSourceInterface;
use Ameax\FieldkitCore\Data\FieldKitFormData;
use Illuminate\Support\Collection;

class FieldKitDefinitionResolver
{
    protected Collection $sources;

    public function __construct()
    {
        $this->sources = new Collection();
        $this->registerSources();
    }

    public function resolve(string $purposeToken): ?FieldKitFormData
    {
        // Try sources in priority order (highest priority first)
        foreach ($this->sources->sortByDesc('priority') as $source) {
            if ($source['instance']->supports($purposeToken)) {
                $definition = $source['instance']->getFormDefinition($purposeToken);
                if ($definition) {
                    return $definition;
                }
            }
        }

        return null;
    }

    public function getAvailablePurposes(): array
    {
        $purposes = [];

        foreach ($this->sources as $source) {
            $purposes = array_merge($purposes, $source['instance']->getAvailablePurposes());
        }

        return array_unique($purposes);
    }

    public function registerSource(FieldKitDefinitionSourceInterface $source): void
    {
        $this->sources->push([
            'instance' => $source,
            'priority' => $source->getPriority(),
        ]);
    }

    protected function registerSources(): void
    {
        $sourceClasses = config('fieldkit.definition_sources', []);

        foreach ($sourceClasses as $sourceClass) {
            if (class_exists($sourceClass)) {
                $this->registerSource(app($sourceClass));
            }
        }
    }
}