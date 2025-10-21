<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Services;

use Ameax\FieldkitCore\Data\FieldKitFormData;
use Ameax\FieldkitCore\FieldKitInputRegistry;

class FieldKitValidationService
{
    public function __construct(
        protected FieldKitInputRegistry $registry,
        protected FieldKitDefinitionResolver $resolver
    ) {}

    /**
     * Get validation rules for a purpose token
     */
    public function getRulesForPurpose(string $purposeToken, array $formData = []): array
    {
        $formDefinition = $this->resolver->resolve($purposeToken);

        if (! $formDefinition) {
            return [];
        }

        return $this->buildValidationRules($formDefinition, $formData);
    }

    /**
     * Get validation messages for a purpose token
     */
    public function getMessagesForPurpose(string $purposeToken, array $formData = []): array
    {
        $formDefinition = $this->resolver->resolve($purposeToken);

        if (! $formDefinition) {
            return [];
        }

        return $this->buildValidationMessages($formDefinition, $formData);
    }

    /**
     * Build validation rules array
     */
    protected function buildValidationRules(FieldKitFormData $formDefinition, array $formData): array
    {
        $rules = [];

        foreach ($formDefinition->fields as $field) {
            // Skip inactive fields
            if (! $field->isActive) {
                continue;
            }

            // Check conditional visibility
            if (! $field->shouldDisplay($formData)) {
                continue;
            }

            $fieldRules = [];

            // Add required rule if field is required
            if ($field->isRequired) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Add input type default rules
            if ($this->registry->has($field->type)) {
                $input = $this->registry->resolve($field->type);
                $defaultRules = $input->getDefaultValidationRules();
                $fieldRules = array_merge($fieldRules, $defaultRules);
            }

            // Add custom validation rules from field definition
            if (! empty($field->validationRules)) {
                $customRules = explode('|', $field->validationRules);
                $fieldRules = array_merge($fieldRules, $customRules);
            }

            // Add options validation for select/radio fields
            if ($this->registry->has($field->type)) {
                $input = $this->registry->resolve($field->type);
                if ($input->supportsOptions() && $field->hasOptions()) {
                    $validValues = $field->options->pluck('value')->toArray();
                    $fieldRules[] = 'in:'.implode(',', $validValues);
                }
            }

            // Remove duplicates and empty rules
            $fieldRules = array_filter(array_unique($fieldRules));

            if (! empty($fieldRules)) {
                $rules[$field->key] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Build validation messages array
     */
    protected function buildValidationMessages(FieldKitFormData $formDefinition, array $formData): array
    {
        $messages = [];

        foreach ($formDefinition->fields as $field) {
            if (! $field->isActive) {
                continue;
            }

            if (! $field->shouldDisplay($formData)) {
                continue;
            }

            $label = $field->label;

            // Common messages
            $messages["{$field->key}.required"] = "The {$label} field is required.";
            $messages["{$field->key}.email"] = "The {$label} must be a valid email address.";
            $messages["{$field->key}.numeric"] = "The {$label} must be a number.";
            $messages["{$field->key}.boolean"] = "The {$label} must be true or false.";
            $messages["{$field->key}.string"] = "The {$label} must be text.";
            $messages["{$field->key}.in"] = "The selected {$label} is invalid.";

            // Type-specific messages
            if ($this->registry->has($field->type)) {
                $input = $this->registry->resolve($field->type);

                if ($input->getName() === 'email') {
                    $messages["{$field->key}.email"] = "Please enter a valid email address for {$label}.";
                } elseif ($input->getName() === 'number') {
                    $messages["{$field->key}.numeric"] = "The {$label} must be a valid number.";
                    $messages["{$field->key}.min"] = "The {$label} must be at least :min.";
                    $messages["{$field->key}.max"] = "The {$label} may not be greater than :max.";
                }
            }
        }

        return $messages;
    }

    /**
     * Validate form data for a purpose token
     */
    public function validate(string $purposeToken, array $formData): array
    {
        $rules = $this->getRulesForPurpose($purposeToken, $formData);
        $messages = $this->getMessagesForPurpose($purposeToken, $formData);

        $validator = validator($formData, $rules, $messages);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
            ];
        }

        return [
            'valid' => true,
            'errors' => [],
        ];
    }
}
