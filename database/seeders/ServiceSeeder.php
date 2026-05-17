<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Specialty;

class ServiceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Service::query()->count() > 0) {
            return;
        }

        $definitions = [
            ['name' => 'Consulta general', 'price' => 30.00, 'duration' => 30, 'specialties' => ['Medicina General']],
            ['name' => 'Consulta de control', 'price' => 20.00, 'duration' => 20, 'specialties' => ['Medicina General']],
            ['name' => 'Emergencia médica', 'price' => 100.00, 'duration' => 60, 'specialties' => ['Medicina General']],
            ['name' => 'Teleconsulta', 'price' => 25.00, 'duration' => 20, 'specialties' => ['Medicina General']],

            ['name' => 'Evaluación médica integral', 'price' => 60.00, 'duration' => 45, 'specialties' => ['Medicina General']],
            ['name' => 'Control de signos vitales', 'price' => 15.00, 'duration' => 15, 'specialties' => ['Medicina General']],
            ['name' => 'Diagnóstico por laboratorio', 'price' => 40.00, 'duration' => 0, 'specialties' => ['Medicina General']],
            ['name' => 'Interpretación de análisis clínicos', 'price' => 30.00, 'duration' => 20, 'specialties' => ['Medicina General']],
            ['name' => 'Examen preventivo', 'price' => 50.00, 'duration' => 40, 'specialties' => ['Medicina General']],

            ['name' => 'Curación de heridas', 'price' => 35.00, 'duration' => 20, 'specialties' => ['Traumatología', 'Medicina General']],
            ['name' => 'Sutura de heridas leves', 'price' => 80.00, 'duration' => 45, 'specialties' => ['Traumatología']],
            ['name' => 'Inyección intramuscular', 'price' => 10.00, 'duration' => 5, 'specialties' => ['Medicina General']],
            ['name' => 'Nebulización', 'price' => 20.00, 'duration' => 15, 'specialties' => ['Pediatría', 'Medicina General']],
            ['name' => 'Control de presión arterial', 'price' => 15.00, 'duration' => 10, 'specialties' => ['Cardiología', 'Medicina General']],

            ['name' => 'Chequeo preventivo anual', 'price' => 120.00, 'duration' => 60, 'specialties' => ['Medicina General']],
            ['name' => 'Control de enfermedades crónicas', 'price' => 45.00, 'duration' => 30, 'specialties' => ['Medicina General']],
            ['name' => 'Vacunación', 'price' => 10.00, 'duration' => 10, 'specialties' => ['Medicina General', 'Pediatría']],
            ['name' => 'Evaluación nutricional', 'price' => 40.00, 'duration' => 40, 'specialties' => ['Medicina General']],

            ['name' => 'Limpieza dental', 'price' => 50.00, 'duration' => 45, 'specialties' => ['Odontología']],
            ['name' => 'Extracción dental', 'price' => 80.00, 'duration' => 60, 'specialties' => ['Odontología']],
            ['name' => 'Restauración dental', 'price' => 120.00, 'duration' => 60, 'specialties' => ['Odontología']],
            ['name' => 'Endodoncia básica', 'price' => 200.00, 'duration' => 90, 'specialties' => ['Odontología']],
            ['name' => 'Ortodoncia básica', 'price' => 300.00, 'duration' => 60, 'specialties' => ['Odontología']],
        ];

        $specialtyMap = Specialty::query()->pluck('specialty_id', 'name')->toArray();

        foreach ($definitions as $def) {
            $service = Service::query()->updateOrCreate(
                ['name' => $def['name']],
                [
                    'description' => $def['name'],
                    'price' => $def['price'],
                    'duration_minutes' => $def['duration'],
                    'status' => 'activo',
                ]
            );

            $specialtyNames = $def['specialties'] ?? [];
            $ids = [];
            foreach ($specialtyNames as $sn) {
                if (isset($specialtyMap[$sn])) {
                    $ids[] = $specialtyMap[$sn];
                }
            }

            if (! empty($ids)) {
                $service->specialties()->sync($ids);
            }
        }
    }
}
