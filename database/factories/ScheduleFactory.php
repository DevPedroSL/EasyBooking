<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\Barbershop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
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
            'day_of_week' => fake()->numberBetween(1, 6),
            'start_time' => '10:00',
            'end_time' => '20:00',
        ];
    }

    public function weekday(int $day): static
    {
        $startHour = fake()->randomElement(['09:00', '10:00']);
        $endHour = $startHour === '09:00' ? '21:00' : '20:00';

        return $this->state(fn (array $attributes) => [
            'day_of_week' => $day,
            'start_time' => $startHour,
            'end_time' => $endHour,
        ]);
    }
}
