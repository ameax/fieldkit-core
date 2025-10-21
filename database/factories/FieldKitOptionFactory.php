<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Database\Factories;

use Ameax\FieldkitCore\Models\FieldKitDefinition;
use Ameax\FieldkitCore\Models\FieldKitOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class FieldKitOptionFactory extends Factory
{
    protected $model = FieldKitOption::class;

    public function definition(): array
    {
        return [
            'fieldkit_definition_id' => FieldKitDefinition::factory(),
            'value' => $this->faker->unique()->slug(),
            'label' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'icon' => null,
            'external_identifier' => $this->faker->optional()->uuid(),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function withExternalIdentifier(string $identifier): static
    {
        return $this->state(function (array $attributes) use ($identifier) {
            return [
                'external_identifier' => $identifier,
            ];
        });
    }

    public function withoutExternalIdentifier(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'external_identifier' => null,
            ];
        });
    }
}
