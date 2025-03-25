<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->randomNumber(3, false),
            'title' => fake()->sentence(),
            'content' => implode('\n', fake()->paragraphs()),
            'created_at' => fake()->dateTimeThisYear()->format('Y-m-d'),
        ];
    }
}
