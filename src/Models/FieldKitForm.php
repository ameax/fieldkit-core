<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $purpose_token
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property string|null $owner_type
 * @property int|null $owner_id
 * @property int $priority
 * @property array<string, mixed>|null $context_data
 */
class FieldKitForm extends Model
{
    use HasFactory;

    protected $table = 'fieldkit_forms';

    protected $fillable = [
        'purpose_token',
        'name',
        'description',
        'is_active',
        'priority',
        'owner_type',
        'owner_id',
        'context_data',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
            'context_data' => 'array',
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
        /** @var FieldKitDefinition|null */
        return $this->fields()->where('key', $key)->first();
    }

    public static function getModelLabel(): string
    {
        return 'FieldKit Form';
    }

    public static function getModelLabelPlural(): string
    {
        return 'FieldKit Forms';
    }

    public static function getNavigationSettings(): array
    {
        return [
            'group' => 'System',
            'icon' => 'heroicon-o-document-text',
            'sort' => 900,
        ];
    }
}
