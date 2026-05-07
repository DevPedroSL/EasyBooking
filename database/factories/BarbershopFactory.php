<?php

namespace Database\Factories;

use App\Models\Barbershop;
use App\Models\Schedules;
use App\Models\Services;
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
        $district = fake()->randomElement([
            'Centro', 'Norte', 'Sur', 'Old Town', 'Studio', 'Club', 'House', 'Lounge', 'Lab', 'Corner',
        ]);

        $city = fake()->randomElement([
            'Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Murcia', 'Malaga', 'Zaragoza', 'Alicante', 'Granada', 'Bilbao',
        ]);

        $nameBase = fake()->unique()->lastName();

        return [
            'barber_id' => User::factory()->barber(),
            'name' => sprintf('%s Barber %s', $nameBase, $district),
            'Description' => fake()->randomElement([
                'Cortes modernos con atencion cercana.',
                'Especialistas en fades, barba y acabados limpios.',
                'Barberia urbana con servicios rapidos y precisos.',
                'Ambiente cuidado y estilo actual para todos los dias.',
                'Cortes clasicos y modernos con trato profesional.',
            ]),
            'address' => sprintf('%s %s, %s', fake()->streetName(), fake()->buildingNumber(), $city),
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
            Services::factory()->create([
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
            Schedules::factory()
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
            ['name' => 'Skin fade', 'description' => 'Degradado marcado con acabado moderno.', 'duration' => 35, 'price' => 18.00],
            ['name' => 'Arreglo de barba', 'description' => 'Recorte y perfilado de barba.', 'duration' => 20, 'price' => 10.00],
            ['name' => 'Corte y barba', 'description' => 'Servicio combinado para renovar tu look.', 'duration' => 50, 'price' => 24.00],
            ['name' => 'Lavado y peinado', 'description' => 'Lavado rapido con peinado final.', 'duration' => 15, 'price' => 7.00],
            ['name' => 'Tinte', 'description' => 'Aplicacion de color con acabado natural.', 'duration' => 60, 'price' => 35.00],
            ['name' => 'Afeitado clasico', 'description' => 'Afeitado tradicional con acabado suave.', 'duration' => 30, 'price' => 14.00],
            ['name' => 'Corte infantil', 'description' => 'Corte comodo y rapido para ninos.', 'duration' => 20, 'price' => 11.00],
        ];
    }
}
