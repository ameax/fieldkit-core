<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\DefinitionSources;

use Ameax\FieldkitCore\Contracts\FieldKitDefinitionSourceInterface;
use Ameax\FieldkitCore\Data\FieldKitFormData;
use Illuminate\Support\Facades\File;

class JsonDefinitionSource implements FieldKitDefinitionSourceInterface
{
    private string $basePath;

    public function __construct()
    {
        $this->basePath = config('fieldkit.json_definitions_path', resource_path('fieldkit'));
    }

    public function getFormDefinition(string $purposeToken): ?FieldKitFormData
    {
        $filePath = $this->getFilePath($purposeToken);

        if (! File::exists($filePath)) {
            return null;
        }

        $content = File::get($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return FieldKitFormData::fromConfig($purposeToken, $data);
    }

    public function getAvailablePurposes(): array
    {
        if (! File::exists($this->basePath)) {
            return [];
        }

        $files = File::files($this->basePath);
        $purposes = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $purposes[] = $file->getFilenameWithoutExtension();
            }
        }

        return $purposes;
    }

    public function getPriority(): int
    {
        return 50; // Lower than config and database
    }

    public function supports(string $purposeToken): bool
    {
        return File::exists($this->getFilePath($purposeToken));
    }

    private function getFilePath(string $purposeToken): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.$purposeToken.'.json';
    }
}
