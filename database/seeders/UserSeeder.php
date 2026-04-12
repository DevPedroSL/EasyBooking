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
            'email' => 'carlos@example.com',
            'password' => Hash::make('password'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        $barber2 = User::create([
            'name' => 'Javier López',
            'email' => 'javier@example.com',
            'password' => Hash::make('password'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        $barber3 = User::create([
            'name' => 'Antonio García',
            'email' => 'antonio@example.com',
            'password' => Hash::make('password'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        // Create some customers
        User::factory(10)->create(['role' => 'customer']);
    }
}