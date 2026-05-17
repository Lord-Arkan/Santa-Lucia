<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrador Santa Lucia',
                'email' => 'admin@santalucia.test',
                'rol' => 'administrador',
            ],
            [
                'name' => 'Asistente Clinica',
                'email' => 'asistente@santalucia.test',
                'rol' => 'asistente',
            ],
            [
                'name' => 'Doctor Garcia',
                'email' => 'doctor@santalucia.test',
                'rol' => 'doctor',
            ],
            [
                'name' => 'Contador Principal',
                'email' => 'contador@santalucia.test',
                'rol' => 'contador',
            ],
            [
                'name' => 'Jefe de Area',
                'email' => 'jefe.area@santalucia.test',
                'rol' => 'jefe_area',
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'rol' => 'administrador',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    ...$user,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'profile_photo_path' => null,
                ],
            );
        }

        // Seed specialties
        $this->call([\Database\Seeders\SpecialtySeeder::class]);
    }
}
