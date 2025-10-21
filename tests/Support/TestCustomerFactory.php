<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestCustomerFactory extends Factory
{
    protected $model = TestCustomer::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'xcu_newsletter' => 0,
            'fieldkit_data' => null,
        ];
    }
}
