<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create specific barbers
        $barber1 = User::create([
            'name' => 'Carlos Martínez',
            'email' => 'barber@example.com',
            'password' => Hash::make('123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        $barber2 = User::create([
            'name' => 'Javier López',
            'email' => 'javier@example.com',
            'password' => Hash::make('123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        $barber3 = User::create([
            'name' => 'Antonio García',
            'email' => 'antonio@example.com',
            'password' => Hash::make('123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        // Create a specific customer
        User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => Hash::make('123'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        // Create some customers
        User::factory(9)->create(['role' => 'customer']);
    }
}
