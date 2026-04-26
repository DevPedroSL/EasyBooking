<?php

namespace Database\Seeders;

use App\Models\Barbershop;
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
        // Get the specific barbers
        $barber = \App\Models\User::where('email', 'barber@example.com')->first();
        $javier = \App\Models\User::where('email', 'javier@example.com')->first();
        $antonio = \App\Models\User::where('email', 'antonio@example.com')->first();

        // Create specific barbershops
        $barbershop1 = \App\Models\Barbershop::updateOrCreate([
            'name' => 'Barbería El Corte Fino',
        ], [
            'barber_id' => $barber->id,
            'Description' => 'Especialistas en cortes modernos y clásicos.',
            'address' => 'Calle 5, Madrid',
            'phone' => '612 345 678',
        ]);

        // Create services for this barbershop
        \App\Models\Services::updateOrCreate([
            'barbershop_id' => $barbershop1->id,
            'name' => 'Corte',
        ], [
            'description' => 'Corte de cabello profesional',
            'duration' => 30,
            'price' => 15.00,
        ]);

        \App\Models\Services::updateOrCreate([
            'barbershop_id' => $barbershop1->id,
            'name' => 'Tinte',
        ], [
            'description' => 'Tinte de cabello completo',
            'duration' => 60,
            'price' => 35.00,
        ]);

        // Create schedules for barbershop1 (Mon-Fri 9AM-9PM)
        for ($day = 1; $day <= 5; $day++) {
            \App\Models\Schedules::updateOrCreate([
                'barbershop_id' => $barbershop1->id,
                'day_of_week' => $day,
            ], [
                'start_time' => '09:00',
                'end_time' => '21:00',
            ]);
        }

        $barbershop2 = \App\Models\Barbershop::updateOrCreate([
            'name' => 'Urban Style Barber',
        ], [
            'barber_id' => $javier->id,
            'Description' => 'Estilo urbano, degradados y barba profesional.',
            'address' => 'Calle 10, Barcelona',
            'phone' => '634 987 321',
        ]);

        // Create services for this barbershop
        \App\Models\Services::updateOrCreate([
            'barbershop_id' => $barbershop2->id,
            'name' => 'Corte',
        ], [
            'description' => 'Corte de cabello profesional',
            'duration' => 30,
            'price' => 15.00,
        ]);

        \App\Models\Services::updateOrCreate([
            'barbershop_id' => $barbershop2->id,
            'name' => 'Tinte',
        ], [
            'description' => 'Tinte de cabello completo',
            'duration' => 60,
            'price' => 35.00,
        ]);

        // Create schedules for barbershop2
        for ($day = 1; $day <= 5; $day++) {
            \App\Models\Schedules::updateOrCreate([
                'barbershop_id' => $barbershop2->id,
                'day_of_week' => $day,
            ], [
                'start_time' => '09:00',
                'end_time' => '21:00',
            ]);
        }

        $barbershop3 = \App\Models\Barbershop::updateOrCreate([
            'name' => 'Old School Barbershop',
        ], [
            'barber_id' => $antonio->id,
            'Description' => 'Ambiente clásico con afeitado y corte tradicional.',
            'address' => 'Calle 7, Jumilla, Murcia',
            'phone' => '655 112 233',
        ]);

        // Create services for this barbershop
        \App\Models\Services::updateOrCreate([
            'barbershop_id' => $barbershop3->id,
            'name' => 'Corte',
        ], [
            'description' => 'Corte de cabello profesional',
            'duration' => 30,
            'price' => 15.00,
        ]);

        \App\Models\Services::updateOrCreate([
            'barbershop_id' => $barbershop3->id,
            'name' => 'Tinte',
        ], [
            'description' => 'Tinte de cabello completo',
            'duration' => 60,
            'price' => 35.00,
        ]);

        // Create schedules for barbershop3
        for ($day = 1; $day <= 5; $day++) {
            \App\Models\Schedules::updateOrCreate([
                'barbershop_id' => $barbershop3->id,
                'day_of_week' => $day,
            ], [
                'start_time' => '09:00',
                'end_time' => '21:00',
            ]);
        }
    }
}
