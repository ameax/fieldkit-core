<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Database\Factories;

use Ameax\FieldkitCore\Models\FieldKitDefinition;
use Ameax\FieldkitCore\Models\FieldKitForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class FieldKitDefinitionFactory extends Factory
{
    protected $model = FieldKitDefinition::class;

    public function definition(): array
    {
        return [
            'fieldkit_form_id' => FieldKitForm::factory(),
            'key' => $this->faker->unique()->slug(),
            'type' => $this->faker->randomElement(['text', 'email', 'select', 'radio', 'checkbox', 'textarea']),
            'label' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'placeholder' => $this->faker->sentence(),
            'is_required' => $this->faker->boolean(),
            'validation_rules' => null,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_active' => true,
            'conditions' => null,
            'mappings' => null,
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

    public function withConditions(array $conditions): static
    {
        return $this->state(function (array $attributes) use ($conditions) {
            return [
                'conditions' => $conditions,
            ];
        });
    }

    public function select(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'select',
            ];
        });
    }

    public function required(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_required' => true,
            ];
        });
    }
}
