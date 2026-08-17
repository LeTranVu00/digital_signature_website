<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedAdmin();

        if (app()->environment('production')) {
            return;
        }

        $this->seedDemoUser();
    }

    private function seedAdmin(): void
    {
        $name = trim((string) env('ADMIN_NAME', 'Administrator'));
        $email = trim((string) env('ADMIN_EMAIL'));
        $password = (string) env('ADMIN_PASSWORD');

        if ($email === '' || $password === '') {
            if (! app()->environment('production')) {
                return;
            }

            throw new RuntimeException(
                'Set ADMIN_EMAIL and ADMIN_PASSWORD before running UserSeeder in production.'
            );
        }

        if (strlen($password) < 12) {
            throw new RuntimeException(
                'ADMIN_PASSWORD must be a unique production password with at least 12 characters.'
            );
        }

        User::updateOrCreate(['email' => $email], [
            'name' => $name !== '' ? $name : 'Administrator',
            'email_verified_at' => now(),
            'password' => Hash::make($password),
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function seedDemoUser(): void
    {
        $name = trim((string) env('SEED_USER_NAME', 'Demo User'));
        $email = trim((string) env('SEED_USER_EMAIL'));
        $password = (string) env('SEED_USER_PASSWORD');

        if ($email === '' || $password === '') {
            return;
        }

        if (strlen($password) < 12) {
            throw new RuntimeException(
                'SEED_USER_PASSWORD must have at least 12 characters.'
            );
        }

        User::updateOrCreate(['email' => $email], [
            'name' => $name !== '' ? $name : 'Demo User',
            'password' => Hash::make($password),
            'role' => 'user',
            'status' => 'active',
        ]);
    }
}
