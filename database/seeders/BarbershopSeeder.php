<?php

namespace Database\Seeders;

use App\Models\Barbershop;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        $barber = User::updateOrCreate([
            'email' => 'devpedrosl@gmail.com',
        ], [
            'name' => 'Barber User',
            'phone' => '600000002',
            'password' => Hash::make('123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        $barbershop = Barbershop::updateOrCreate([
            'name' => 'Barberia 1',
        ], [
            'barber_id' => $barber->id,
            'address' => 'Calle 5, Madrid',
            'phone' => '612345678',
            'slot_interval_minutes' => 60,
            'visibility' => 'public',
            'is_approved' => true,
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
            Service::updateOrCreate([
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
            Schedule::updateOrCreate([
                'barbershop_id' => $barbershop->id,
                'day_of_week' => $day,
            ], [
                'start_time' => '09:00',
                'end_time' => '21:00',
            ]);
        }
    }
}
