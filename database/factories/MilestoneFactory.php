<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Milestone>
 */
class MilestoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Office', 'Headquarters', 'Branch', 'Store']),
            'longitudinal' => $this->faker->longitude(),
            'latitudinal' => $this->faker->latitude(),
            'favorite' => $this->faker->boolean(20),
        ];
    }

    /**
     * Indicate that the milestone is a favorite.
     */
    public function favorite(): static
    {
        return $this->state(function () {
            return [
                'favorite' => true,
            ];
        });
    }
}
