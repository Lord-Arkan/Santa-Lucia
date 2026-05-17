<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specialty;

class SpecialtySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $items = [
            'Medicina General',
            'Pediatría',
            'Ginecología',
            'Cardiología',
            'Dermatología',
            'Oftalmología',
            'Traumatología',
            'Neurología',
            'Psiquiatría',
            'Odontología',
        ];

        foreach ($items as $name) {
            Specialty::query()->updateOrCreate(
                ['name' => $name],
                ['status' => 'activo']
            );
        }
    }
}
