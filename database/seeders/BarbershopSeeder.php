<?php

namespace Database\Seeders;

use App\Models\Barbershop;
use App\Models\Schedules;
use App\Models\Services;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BarbershopSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedPrimaryBarbershop();

        $barbershopsToCreate = max(0, 20 - Barbershop::count());

        if ($barbershopsToCreate > 0) {
            Barbershop::factory()->count($barbershopsToCreate)->create();
        }
    }

    private function seedPrimaryBarbershop(): void
    {
        $barber = User::where('email', 'barber@example.com')->first();

        if (! $barber) {
            return;
        }

        $barbershop = Barbershop::updateOrCreate([
            'name' => 'Barberia 1',
        ], [
            'barber_id' => $barber->id,
            'Description' => 'Cortes buenos',
            'address' => 'Calle 5, Madrid',
            'phone' => '612345678',
            'slot_interval_minutes' => 60,
            'visibility' => 'public',
        ]);

        $services = [
            [
                'name' => 'Corte',
                'description' => 'Corte de pelo clasico.',
                'duration' => 30,
                'price' => 15.00,
            ],
            [
                'name' => 'Rulos',
                'description' => 'Rulos permanentes.',
                'duration' => 60,
                'price' => 25.00,
            ],
            [
                'name' => 'Tinte',
                'description' => 'Aplicacion de tinte capilar.',
                'duration' => 60,
                'price' => 35.00,
            ],
        ];

        foreach ($services as $service) {
            Services::updateOrCreate([
                'barbershop_id' => $barbershop->id,
                'name' => $service['name'],
            ], [
                'description' => $service['description'],
                'duration' => $service['duration'],
                'price' => $service['price'],
                'visibility' => 'public',
            ]);
        }

        foreach ([1, 2, 3, 4, 5] as $day) {
            Schedules::updateOrCreate([
                'barbershop_id' => $barbershop->id,
                'day_of_week' => $day,
            ], [
                'start_time' => '09:00',
                'end_time' => '21:00',
            ]);
        }
    }
}
