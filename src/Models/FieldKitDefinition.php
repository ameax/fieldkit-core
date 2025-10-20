<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldKitDefinition extends Model
{
    protected $table = 'fieldkit_definitions';

    protected $fillable = [
        'fieldkit_form_id',
        'key',
        'type',
        'label',
        'description',
        'placeholder',
        'is_required',
        'validation_rules',
        'sort_order',
        'is_active',
        'conditions',
        'mappings',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'conditions' => 'array',
            'mappings' => 'array',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FieldKitForm::class, 'fieldkit_form_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(FieldKitOption::class, 'fieldkit_definition_id')
            ->orderBy('sort_order');
    }

    public function getValidationRules(): array
    {
        $rules = [];

        if ($this->validation_rules) {
            $rules = explode('|', $this->validation_rules);
        }

        if ($this->is_required) {
            $rules[] = 'required';
        }

        return $rules;
    }

    public function needsOptions(): bool
    {
        return in_array($this->type, ['select', 'radio']);
    }

    /**
     * Check if this field should be displayed based on conditions
     *
     * @param array $formData All form data (native + fieldkit)
     * @return bool
     */
    public function shouldDisplay(array $formData = []): bool
    {
        if (empty($this->conditions)) {
            return true;  // No conditions = always show
        }

        // ALL conditions must be met (AND)
        foreach ($this->conditions as $condition) {
            $fieldKey = $condition['field_key'] ?? null;
            $expectedValues = $condition['answer_values'] ?? [];
            $operator = $condition['operator'] ?? 'in';

            // Dependent field not present in data
            if (!isset($formData[$fieldKey])) {
                return false;
            }

            $actualValue = $formData[$fieldKey];

            // Value normalization (bool → string)
            if (is_bool($actualValue)) {
                $actualValue = $actualValue ? 'true' : 'false';
            }

            switch ($operator) {
                case 'in':
                    if (!in_array($actualValue, $expectedValues, true)) {
                        return false;
                    }
                    break;

                case 'not_in':
                    if (in_array($actualValue, $expectedValues, true)) {
                        return false;
                    }
                    break;

                case 'equals':
                    $expectedValue = $expectedValues[0] ?? null;
                    if ($actualValue !== $expectedValue) {
                        return false;
                    }
                    break;

                case 'not_equals':
                    $expectedValue = $expectedValues[0] ?? null;
                    if ($actualValue === $expectedValue) {
                        return false;
                    }
                    break;

                default:
                    return false;  // Unknown operator
            }
        }

        return true;  // All conditions met
    }

    public static function getModelLabel(): string
    {
        return 'Field Definition';
    }

    public static function getModelLabelPlural(): string
    {
        return 'Field Definitions';
    }

    public static function getNavigationSettings(): array
    {
        return [
            'group' => 'System',
            'icon' => 'heroicon-o-adjustments-horizontal',
            'sort' => 901,
        ];
    }
}