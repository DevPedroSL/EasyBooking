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
        $barbershop = \App\Models\Barbershop::inRandomOrder()->first() ?? \App\Models\Barbershop::factory()->create();
        $service = \App\Models\Services::where('barbershop_id', $barbershop->id)->inRandomOrder()->first()
            ?? \App\Models\Services::factory()->create(['barbershop_id' => $barbershop->id]);

        return [
            'client_id' => User::where('role', 'customer')->inRandomOrder()->first()->id ?? User::factory()->state(['role' => 'customer'])->create()->id,
            'barbershop_id' => $barbershop->id,
            'service_id' => $service->id,
            'appointment_date' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'start_time' => $start,
            'end_time' => $end,
            'client_comment' => fake()->optional()->text(50),
            'status' => fake()->randomElement(['pending', 'accepted', 'rejected', 'completed', 'cancelled']),
        ];
    }
}
