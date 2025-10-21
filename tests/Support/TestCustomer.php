<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestCustomer extends Model
{
    use HasFactory;

    protected $table = 'test_customers';

    protected $fillable = [
        'email',
        'phone',
        'xcu_newsletter',
        'fieldkit_data',
    ];

    protected function casts(): array
    {
        return [
            'fieldkit_data' => 'array',
            'xcu_newsletter' => 'integer',
        ];
    }

    public static function newFactory()
    {
        return new TestCustomerFactory;
    }
}
