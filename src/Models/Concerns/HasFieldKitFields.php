<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Models\Concerns;

trait HasFieldKitFields
{
    public function initializeHasFieldKitFields(): void
    {
        $this->casts['fieldkit_data'] = 'array';
    }

    public function getFieldKitValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->fieldkit_data, $key, $default);
    }

    public function setFieldKitValue(string $key, mixed $value): void
    {
        $data = $this->fieldkit_data ?? [];
        data_set($data, $key, $value);
        $this->fieldkit_data = $data;
    }

    public function setFieldKitValues(array $values): void
    {
        $data = $this->fieldkit_data ?? [];

        foreach ($values as $key => $value) {
            data_set($data, $key, $value);
        }

        $this->fieldkit_data = $data;
    }
}