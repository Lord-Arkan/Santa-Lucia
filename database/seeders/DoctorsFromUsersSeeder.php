<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Support\Str;

class DoctorsFromUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = Specialty::query()->where('status', 'activo')->pluck('specialty_id')->toArray();

        if (empty($specialties)) {
            $this->command->info('No hay especialidades activas. Omite creación de doctores.');
            return;
        }

        $users = User::query()->where('rol', 'doctor')->get();
        $created = 0;

        foreach ($users as $user) {
            $exists = Doctor::query()->where('user_id', $user->id)->exists();
            if ($exists) {
                continue;
            }

            $specialty_id = $specialties[array_rand($specialties)];
            $license = 'LIC-' . strtoupper(Str::random(6));

            Doctor::create([
                'user_id' => $user->id,
                'specialty_id' => $specialty_id,
                'license_number' => $license,
                'status' => 'activo',
            ]);

            $created++;
        }

        $this->command->info("Doctores creados: {$created}");
    }
}
