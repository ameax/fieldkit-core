<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldKitOption extends Model
{
    protected $table = 'fieldkit_options';

    protected $fillable = [
        'fieldkit_definition_id',
        'value',
        'label',
        'description',
        'icon',
        'external_identifier',
        'sort_order',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(FieldKitDefinition::class, 'fieldkit_definition_id');
    }

    /**
     * Returns the external ID (fallback to value)
     */
    public function getExternalIdentifier(): string
    {
        return $this->external_identifier ?? $this->value;
    }
}