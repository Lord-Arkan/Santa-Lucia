<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Support\ModuleCatalog;

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
                'name' => 'Administrador',
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
                'name' => 'Doctor Lopez',
                'email' => 'doctor.lopez@santalucia.test',
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
                'name' => 'Franklin Lopez',
                'email' => 'franklin@example.com',
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
                    'module_permissions' => ModuleCatalog::defaultsForRole($user['rol']),
                ],
            );
        }

        // Seed specialties and synchronize doctors from users
        $this->call([
            \Database\Seeders\SpecialtySeeder::class,
            \Database\Seeders\ServiceSeeder::class,
            \Database\Seeders\DoctorsFromUsersSeeder::class,
            \Database\Seeders\ClinicalReportsDemoSeeder::class,
        ]);
    }
}
