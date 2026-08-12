<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]);

        // User
        User::create([
            'name' => 'Nguyen Van A',
            'email' => 'user@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
        ]);
    }
}
