<?php

namespace Database\Factories;

use App\Models\Appointments;
use App\Models\User;
use App\Models\Services;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointments>
 */
class AppointmentsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->time('H:i');
        $end = date('H:i', strtotime($start) + 3600); // 1 hour later

        return [
            'client_id' => User::where('role', 'customer')->inRandomOrder()->first()->id ?? User::factory()->state(['role' => 'customer'])->create()->id,
            'barbershop_id' => \App\Models\Barbershop::inRandomOrder()->first()->id ?? \App\Models\Barbershop::factory()->create()->id,
            'service_id' => \App\Models\Services::inRandomOrder()->first()->id ?? \App\Models\Services::factory()->create()->id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => fake()->randomElement(['pending', 'accepted', 'rejected', 'completed', 'cancelled']),
        ];
    }
}