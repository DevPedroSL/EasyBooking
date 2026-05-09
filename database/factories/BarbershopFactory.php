<?php

namespace Database\Factories;

use App\Models\Barbershop;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barbershop>
 */
class BarbershopFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $district = fake()->randomElement([
            'Centro', 'Norte', 'Sur', 'Este', 'Oeste', 'Casco Antiguo', 'Ensanche', 'Mercado', 'Puerto', 'Estacion',
        ]);

        $city = fake()->randomElement([
            'Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Murcia', 'Malaga', 'Zaragoza', 'Alicante', 'Granada', 'Bilbao',
        ]);

        $nameBase = fake()->randomElement([
            'La Navaja', 'El Peine Fino', 'Corte Maestro', 'La Esquina', 'Buen Corte', 'Afeitado Real', 'Tijera de Oro',
            'Estilo Urbano', 'El Sillon', 'Barba y Corte', 'La Barberia del Barrio', 'Corte Clasico', 'Linea Fina',
            'Navaja Noble', 'Punto Barberia', 'El Ritual', 'Manos Expertas', 'La Recortadora', 'Estilo y Barba',
            'Cabello Fino', 'El Perfilado', 'Barberia Avenida', 'La Casa del Corte', 'Corte del Norte', 'Barba Maestra',
        ]);

        return [
            'barber_id' => User::factory()->barber(),
            'name' => sprintf('%s %s %04d', $nameBase, $district, fake()->unique()->numberBetween(1, 9999)),
            'address' => sprintf('%s %s, %s', fake()->randomElement($this->spanishStreetNames()), fake()->buildingNumber(), $city),
            'phone' => fake()->numerify('6########'),
            'slot_interval_minutes' => 60,
            'visibility' => 'public',
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Barbershop $barbershop) {
            $this->createServices($barbershop);
            $this->createSchedules($barbershop);
        });
    }

    private function createServices(Barbershop $barbershop): void
    {
        $services = fake()->randomElements($this->serviceCatalog(), 3);

        foreach ($services as $service) {
            Service::factory()->create([
                'barbershop_id' => $barbershop->id,
                'name' => $service['name'],
                'description' => $service['description'],
                'duration' => $service['duration'],
                'price' => $service['price'],
            ]);
        }
    }

    private function createSchedules(Barbershop $barbershop): void
    {
        $days = fake()->randomElement([
            [1, 2, 3, 4, 5],
            [1, 2, 3, 4, 5, 6],
            [2, 3, 4, 5, 6],
        ]);

        foreach ($days as $day) {
            Schedule::factory()
                ->weekday($day)
                ->create([
                    'barbershop_id' => $barbershop->id,
                ]);
        }
    }

    private function serviceCatalog(): array
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

    private function spanishStreetNames(): array
    {
        return [
            'Calle Mayor',
            'Calle del Sol',
            'Calle Real',
            'Avenida de la Constitucion',
            'Paseo de la Alameda',
            'Calle de la Estacion',
            'Calle Nueva',
            'Plaza de Espana',
            'Avenida del Mediterraneo',
            'Calle del Mercado',
            'Ronda Norte',
            'Calle de los Olivos',
            'Avenida de Andalucia',
            'Calle San Miguel',
            'Paseo Maritimo',
        ];
    }
}
