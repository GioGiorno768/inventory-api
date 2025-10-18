<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@inventory.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Staff
        User::create([
            'name' => 'Staff Gudang',
            'email' => 'staff@inventory.com',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
        ]);
    }
}