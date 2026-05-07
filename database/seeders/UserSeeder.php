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
        User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'phone' => '600000001',
            'password' => Hash::make('123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate([
            'email' => 'customer@example.com',
        ], [
            'name' => 'Customer User',
            'phone' => '600000005',
            'password' => Hash::make('123'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate([
            'email' => 'barber@example.com',
        ], [
            'name' => 'Barber User',
            'phone' => '600000002',
            'password' => Hash::make('123'),
            'role' => 'barber',
            'email_verified_at' => now(),
        ]);

        $customersToCreate = max(0, 10 - User::where('role', 'customer')->count());

        if ($customersToCreate > 0) {
            User::factory($customersToCreate)->customer()->create();
        }
    }
}
