<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldKitForm extends Model
{
    protected $fillable = [
        'purpose_token',
        'name',
        'description',
        'is_active',
        'owner_type',
        'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FieldKitDefinition::class, 'fieldkit_form_id')
            ->orderBy('sort_order');
    }

    public function activeFields(): HasMany
    {
        return $this->fields()->where('is_active', true);
    }

    public function scopeByPurpose($query, string $purposeToken)
    {
        return $query->where('purpose_token', $purposeToken);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getField(string $key): ?FieldKitDefinition
    {
        return $this->fields()->where('key', $key)->first();
    }
}