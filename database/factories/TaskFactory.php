<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Task;
/**
 * @extends Factory<Task>
 */
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['todo', 'in_progress', 'completed', 'due']),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'due_time' => $this->faker->optional(0.7)->time('H:i'), // ৭০% সম্ভাবনা সময় থাকার, ফরম্যাট: HH:MM
        ];
    }
}
