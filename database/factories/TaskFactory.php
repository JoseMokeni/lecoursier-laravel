<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $completed = $this->faker->boolean(20);
        $completedAt = $completed ? $this->faker->dateTimeBetween('-1 month', 'now') : null;
        $dueDate = $this->faker->dateTimeBetween('now', '+1 month');

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'canceled']),
            'completed' => $completed,
            'completed_at' => $completedAt,
            'due_date' => $dueDate,
            'answered_at' => $this->faker->optional(30)->dateTimeBetween('-2 weeks', 'now'),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
        ];
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(function () {
            return [
                'completed' => true,
                'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'status' => 'completed',
            ];
        });
    }

    /**
     * Indicate that the task is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(function () {
            return [
                'priority' => 'high',
            ];
        });
    }
}
