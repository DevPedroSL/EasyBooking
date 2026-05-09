<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * La contrasena actual usada por el factory.
     */
    protected static ?string $password;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement($this->spanishNames()) . ' ' . fake()->randomElement($this->spanishSurnames()),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('6########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement(['admin', 'barber', 'customer']),
            'barbershop_id' => null,
        ];
    }

    /**
     * Indica que el email del usuario no esta verificado.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function barber(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'barber',
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'customer',
        ]);
    }

    private function spanishNames(): array
    {
        return [
            'Alejandro', 'Lucia', 'Daniel', 'Sofia', 'Hugo', 'Martina', 'Pablo', 'Valeria', 'Adrian', 'Paula',
            'Javier', 'Carmen', 'Diego', 'Claudia', 'Marcos', 'Alba', 'Mario', 'Nerea', 'Sergio', 'Irene',
            'Manuel', 'Laura', 'Ruben', 'Marta', 'Carlos', 'Elena', 'Ivan', 'Sara', 'Miguel', 'Noelia',
        ];
    }

    private function spanishSurnames(): array
    {
        return [
            'Garcia', 'Martinez', 'Lopez', 'Sanchez', 'Perez', 'Gomez', 'Martin', 'Jimenez', 'Ruiz', 'Hernandez',
            'Diaz', 'Moreno', 'Alvarez', 'Romero', 'Navarro', 'Torres', 'Dominguez', 'Vazquez', 'Ramos', 'Gil',
            'Serrano', 'Molina', 'Blanco', 'Castro', 'Ortega', 'Delgado', 'Rubio', 'Marin', 'Iglesias', 'Medina',
        ];
    }
}
