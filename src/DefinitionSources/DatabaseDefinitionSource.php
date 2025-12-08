<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\DefinitionSources;

use Ameax\FieldkitCore\Contracts\ContextResolverInterface;
use Ameax\FieldkitCore\Contracts\FieldKitDefinitionSourceInterface;
use Ameax\FieldkitCore\Data\FieldKitFormData;
use Ameax\FieldkitCore\Models\FieldKitForm;

class DatabaseDefinitionSource implements FieldKitDefinitionSourceInterface
{
    public function getFormDefinition(string $purposeToken): ?FieldKitFormData
    {
        $query = FieldKitForm::byPurpose($purposeToken)
            ->active()
            ->with(['fields.options']);

        $form = $this->applyContextFilter($query)->first();

        if (! $form) {
            return null;
        }

        return FieldKitFormData::fromModel($form);
    }

    public function getAvailablePurposes(): array
    {
        $query = FieldKitForm::active();

        return $this->applyContextFilter($query)
            ->pluck('purpose_token')
            ->toArray();
    }

    public function getPriority(): int
    {
        return 100; // Highest priority
    }

    public function supports(string $purposeToken): bool
    {
        $query = FieldKitForm::byPurpose($purposeToken)->active();

        return $this->applyContextFilter($query)->exists();
    }

    /**
     * Apply context filtering to query if enabled
     *
     * @param  \Illuminate\Database\Eloquent\Builder<FieldKitForm>  $query
     * @return \Illuminate\Database\Eloquent\Builder<FieldKitForm>
     */
    protected function applyContextFilter($query)
    {
        if (! $this->isContextEnabled()) {
            return $query;
        }

        $resolver = $this->getContextResolver();
        if (! $resolver) {
            return $query;
        }

        // Get all forms and filter using resolver
        // We use whereIn with IDs after filtering since context matching
        // requires model data that can't be done in pure SQL
        $matchingIds = $query->get()
            ->filter(fn (FieldKitForm $form) => $resolver->matchesContext($form->context_data))
            ->pluck('id')
            ->toArray();

        return FieldKitForm::whereIn('id', $matchingIds);
    }

    protected function isContextEnabled(): bool
    {
        return (bool) config('fieldkit.context.enabled', false);
    }

    protected function getContextResolver(): ?ContextResolverInterface
    {
        $resolverClass = config('fieldkit.context.resolver');

        if (! $resolverClass || ! class_exists($resolverClass)) {
            return null;
        }

        return app($resolverClass);
    }
}
