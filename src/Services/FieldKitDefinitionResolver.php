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
        $this->sources = new Collection;
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

        foreach ($sourceClasses as $key => $sourceClass) {
            // Handle both string format and array format with class/priority
            if (is_string($sourceClass)) {
                // Simple string format: 'config' => 'ClassName'
                if (class_exists($sourceClass)) {
                    $this->registerSource(app($sourceClass));
                }
            } elseif (is_array($sourceClass)) {
                if (isset($sourceClass['class'])) {
                    // Array format: 'config' => ['class' => 'ClassName', 'priority' => 100]
                    if (class_exists($sourceClass['class'])) {
                        $this->registerSource(app($sourceClass['class']));
                    }
                } else {
                    // The issue might be that the config structure is different
                    // Let's just skip arrays without 'class' key for now
                    continue;
                }
            }
        }
    }
}
