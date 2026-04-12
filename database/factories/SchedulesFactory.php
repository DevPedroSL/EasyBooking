<?php

namespace Database\Factories;

use App\Models\Schedules;
use App\Models\Barbershop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedules>
 */
class SchedulesFactory extends Factory
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
            'day_of_week' => fake()->numberBetween(1, 7), // 1=Monday, 7=Sunday
            'start_time' => fake()->time('H:i'),
            'end_time' => fake()->time('H:i'),
        ];
    }
}