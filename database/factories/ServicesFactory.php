<?php

namespace Database\Factories;

use App\Models\Services;
use App\Models\Barbershop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Services>
 */
class ServicesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'barbershop_id' => Barbershop::inRandomOrder()->first()->id ?? Barbershop::factory()->create()->id,
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'duration' => fake()->numberBetween(15, 120),
            'price' => fake()->randomFloat(2, 10, 100),
        ];
    }
}