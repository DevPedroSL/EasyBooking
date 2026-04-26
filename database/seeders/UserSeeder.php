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
        User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'phone' => '600000001',
            'password' => Hash::make('123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create specific barbers
        User::updateOrCreate([
            'email' => 'barber@example.com',
        ], [
            'name' => 'Barber User',
            'phone' => '600000002',
            'password' => Hash::make('123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate([
            'email' => 'javier@example.com',
        ], [
            'name' => 'Javier López',
            'phone' => '600000003',
            'password' => Hash::make('123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate([
            'email' => 'antonio@example.com',
        ], [
            'name' => 'Antonio García',
            'phone' => '600000004',
            'password' => Hash::make('123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        // Create a specific customer
        User::updateOrCreate([
            'email' => 'customer@example.com',
        ], [
            'name' => 'Customer User',
            'phone' => '600000005',
            'password' => Hash::make('123'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        // Create some customers
        $customersToCreate = max(0, 10 - User::where('role', 'customer')->count());

        if ($customersToCreate > 0) {
            User::factory($customersToCreate)->create(['role' => 'customer']);
        }
    }
}
