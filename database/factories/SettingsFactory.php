<?php

namespace Database\Factories;

use App\Models\Settings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Settings>
 */
class SettingsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $id = "admin_" . fake()->sentence(1);

        return [
            'id' => $id,
            'value' => fake()->randomFloat(2, 10, 50),
            'updated_by' => 1,
            'type' => Settings::DATA_TYPES[0],
            'description' => fake()->sentence()
        ];
    }
}
