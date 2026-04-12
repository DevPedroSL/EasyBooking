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
        $carlos = \App\Models\User::where('email', 'carlos@example.com')->first();
        $javier = \App\Models\User::where('email', 'javier@example.com')->first();
        $antonio = \App\Models\User::where('email', 'antonio@example.com')->first();

        // Create specific barbershops
        \App\Models\Barbershop::create([
            'barber_id' => $carlos->id,
            'name' => 'Barbería El Corte Fino',
            'Description' => 'Especialistas en cortes modernos y clásicos con atención personalizada.',
            'address' => 'Calle Mayor 12, Orihuela, Alicante',
            'phone' => '612 345 678',
        ]);

        \App\Models\Barbershop::create([
            'barber_id' => $javier->id,
            'name' => 'Urban Style Barber',
            'Description' => 'Barbería urbana con estilo moderno, degradados y arreglos de barba profesionales.',
            'address' => 'Avenida de España 45, Elche, Alicante',
            'phone' => '634 987 321',
        ]);

        \App\Models\Barbershop::create([
            'barber_id' => $antonio->id,
            'name' => 'Old School Barbershop',
            'Description' => 'Ambiente clásico con técnicas tradicionales de afeitado y corte.',
            'address' => 'Calle San José 8, Murcia',
            'phone' => '655 112 233',
        ]);
    }
}