<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Contracts;

/**
 * Resolves context at runtime for form filtering
 *
 * Apps implement this to determine the current execution context
 * and match it against form context configurations.
 */
interface ContextResolverInterface
{
    /**
     * Returns the current execution context
     *
     * This is called during form rendering to determine
     * which context values apply to the current request.
     *
     * @return array<string, mixed> Current context values
     */
    public function getCurrentContext(): array;

    /**
     * Checks if a form's context matches the current context
     *
     * @param  array<string, mixed>|null  $formContext  Context data stored with form (null = matches all)
     * @return bool True if form should be visible in current context
     */
    public function matchesContext(?array $formContext): bool;
}
