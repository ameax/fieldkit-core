<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface FieldKitMappingHandlerInterface
{
    /**
     * Can this handler process the mappings?
     *
     * @param  string  $adapter  Adapter type (e.g. 'ameax_column', 'mailchimp_api')
     */
    public function supports(string $adapter): bool;

    /**
     * Process the mapped data
     *
     * @param  Model  $model  Parent model (e.g. Customer)
     * @param  Collection  $mappings  Filtered mappings for this handler
     * @param  array  $formData  Original form data
     */
    public function handle(
        Model $model,
        Collection $mappings,
        array $formData
    ): void;

    /**
     * Should this handler be executed asynchronously (Queue)?
     */
    public function shouldQueue(): bool;
}
