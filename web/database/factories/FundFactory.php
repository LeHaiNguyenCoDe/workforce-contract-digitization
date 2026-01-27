<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fund>
 */
class FundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Fund',
            'code' => $this->faker->unique()->slug(2),
            'type' => 'cash',
            'balance' => 1000,
            'initial_balance' => 1000,
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
