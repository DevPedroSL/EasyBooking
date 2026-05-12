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
        $admin = User::updateOrCreate([
            'email' => 'devpedro17@gmail.com',
        ], [
            'name' => 'Admin User',
            'phone' => '600000001',
            'password' => Hash::make('123'),
        ]);
        $admin->forceFill([
            'role' => 'admin',
            'email_verified_at' => now(),
        ])->save();

        $customer = User::updateOrCreate([
            'email' => 'easybookingpedro@gmail.com',
        ], [
            'name' => 'Customer User',
            'phone' => '600000005',
            'password' => Hash::make('123'),
        ]);
        $customer->forceFill([
            'role' => 'customer',
            'email_verified_at' => now(),
        ])->save();

        $barber = User::updateOrCreate([
            'email' => 'devpedrosl@gmail.com',
        ], [
            'name' => 'Barber User',
            'phone' => '600000002',
            'password' => Hash::make('123'),
        ]);
        $barber->forceFill([
            'role' => 'barber',
            'email_verified_at' => now(),
        ])->save();

        $customersToCreate = max(0, 10 - User::where('role', 'customer')->count());

        if ($customersToCreate > 0) {
            User::factory($customersToCreate)->customer()->create();
        }
    }
}
