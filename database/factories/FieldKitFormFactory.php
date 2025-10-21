<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Database\Factories;

use Ameax\FieldkitCore\Models\FieldKitForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class FieldKitFormFactory extends Factory
{
    protected $model = FieldKitForm::class;

    public function definition(): array
    {
        return [
            'purpose_token' => $this->faker->unique()->slug(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'owner_type' => null,
            'owner_id' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
