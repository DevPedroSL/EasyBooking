<?php

namespace Database\Seeders;

use App\Models\Appointments;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Appointments::count() === 0) {
            Appointments::factory(10)->create();
        }
    }
}
