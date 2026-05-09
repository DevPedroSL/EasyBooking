<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Barbershop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $service = fake()->randomElement($this->catalog());

        return [
            'barbershop_id' => Barbershop::inRandomOrder()->first()->id ?? Barbershop::factory()->create()->id,
            'name' => $service['name'],
            'description' => $service['description'],
            'duration' => $service['duration'],
            'price' => $service['price'],
            'visibility' => 'public',
        ];
    }

    private function catalog(): array
    {
        return [
            ['name' => 'Corte clasico', 'description' => 'Corte limpio para el dia a dia.', 'duration' => 30, 'price' => 15.00],
            ['name' => 'Degradado', 'description' => 'Degradado marcado con acabado moderno.', 'duration' => 35, 'price' => 18.00],
            ['name' => 'Arreglo de barba', 'description' => 'Recorte y perfilado de barba.', 'duration' => 20, 'price' => 10.00],
            ['name' => 'Corte y barba', 'description' => 'Servicio combinado para renovar tu look.', 'duration' => 50, 'price' => 24.00],
            ['name' => 'Lavado y peinado', 'description' => 'Lavado rapido con peinado final.', 'duration' => 15, 'price' => 7.00],
            ['name' => 'Tinte', 'description' => 'Aplicacion de color con acabado natural.', 'duration' => 60, 'price' => 35.00],
            ['name' => 'Afeitado clasico', 'description' => 'Afeitado tradicional con acabado suave.', 'duration' => 30, 'price' => 14.00],
            ['name' => 'Corte infantil', 'description' => 'Corte comodo y rapido para ninos.', 'duration' => 20, 'price' => 11.00],
        ];
    }
}
