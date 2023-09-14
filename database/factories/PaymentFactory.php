<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->text(40),
            'value' => fake()->randomFloat(2, 10, 50),
            'expire_at' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => fake()->randomElement(['active', 'inactive', 'paid', 'cancelled'])
        ];
    }

}
