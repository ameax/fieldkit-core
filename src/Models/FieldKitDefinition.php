<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $fieldkit_form_id
 * @property string $key
 * @property string $type
 * @property string $label
 * @property string|null $description
 * @property string|null $placeholder
 * @property bool $is_required
 * @property string|null $validation_rules
 * @property int $sort_order
 * @property bool $is_active
 * @property array|null $conditions
 * @property array|null $mappings
 */
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
