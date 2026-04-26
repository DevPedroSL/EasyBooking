<?php

namespace Database\Factories;

use App\Models\Barbershop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barbershop>
 */
class BarbershopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'barber_id' => User::factory()->state(['role' => 'barber']),
            'name' => fake()->company(),
            'Description' => fake()->text(50),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
        ];
    }
}
