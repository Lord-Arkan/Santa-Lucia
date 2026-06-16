<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\User;
use App\Support\ModuleCatalog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClinicalReportsDemoSeeder extends Seeder
{
    public function run(): void
    {
        Carbon::setLocale('es');

        $specialties = $this->specialties();
        $doctors = $this->doctors($specialties);
        $services = $this->services($specialties, $doctors);
        $patients = $this->patients();

        Appointment::query()
            ->whereHas('patient', fn ($query) => $query->where('document_number', 'like', 'RPT-%'))
            ->delete();

        $start = Carbon::today()->subDays(89);
        $statuses = ['COMPLETED', 'COMPLETED', 'COMPLETED', 'SCHEDULED', 'CANCELLED', 'NO_SHOW', 'EXPIRED'];
        $created = 0;

        for ($day = 0; $day < 90; $day++) {
            $date = $start->copy()->addDays($day);
            $appointmentsForDay = 1 + (($day * 7) % 4);

            for ($slot = 0; $slot < $appointmentsForDay; $slot++) {
                $doctor = $doctors[($day + $slot) % count($doctors)];
                $patient = $patients[($day * 3 + $slot) % count($patients)];
                $service = $services->first(fn (Service $item) => $item->specialties->contains('specialty_id', $doctor->specialty_id)) ?? $services->first();
                $hour = 8 + (($slot * 2 + $day) % 8);
                $startTime = sprintf('%02d:00:00', $hour);
                $endTime = Carbon::createFromFormat('H:i:s', $startTime)->addMinutes((int) $service->duration_minutes ?: 30)->format('H:i:s');
                $status = $date->isFuture() ? 'SCHEDULED' : $statuses[($day + $slot) % count($statuses)];

                Appointment::query()->create([
                    'patient_id' => $patient->patient_id,
                    'doctor_id' => $doctor->doctor_id,
                    'service_id' => $service->service_id,
                    'appointment_date' => $date->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $status,
                ]);

                $created++;
            }
        }

        $this->command?->info("Citas demo para reportes creadas: {$created}");
    }

    private function specialties(): array
    {
        $names = ['Medicina General', 'Cardiologia', 'Pediatria', 'Dermatologia', 'Traumatologia', 'Odontologia'];

        return collect($names)
            ->map(fn (string $name) => Specialty::query()->firstOrCreate(['name' => $name], ['status' => 'activo']))
            ->all();
    }

    private function doctors(array $specialties): array
    {
        $doctorNames = [
            'Dra. Mariana Alvarez',
            'Dr. Ricardo Salazar',
            'Dra. Patricia Mendoza',
            'Dr. Fernando Castillo',
            'Dra. Claudia Rivas',
            'Dr. Sergio Herrera',
        ];

        return collect($specialties)
            ->map(function (Specialty $specialty, int $index) use ($doctorNames) {
                $user = User::query()->updateOrCreate(
                    ['email' => 'reporte.doctor'.($index + 1).'@santalucia.test'],
                    [
                        'name' => $doctorNames[$index] ?? 'Dr. Santa Lucia '.($index + 1),
                        'rol' => 'doctor',
                        'email_verified_at' => now(),
                        'password' => Hash::make('password'),
                        'module_permissions' => ModuleCatalog::defaultsForRole('doctor'),
                        'status' => true,
                    ],
                );

                $doctor = Doctor::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'specialty_id' => $specialty->specialty_id,
                        'license_number' => 'RPT-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                        'status' => 'activo',
                    ],
                );

                foreach (['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'] as $day) {
                    DoctorSchedule::query()->updateOrCreate(
                        ['doctor_id' => $doctor->doctor_id, 'day_of_week' => $day],
                        ['start_time' => '08:00:00', 'end_time' => '18:00:00', 'status' => 'activo'],
                    );
                }

                return $doctor;
            })
            ->all();
    }

    private function services(array $specialties, array $doctors)
    {
        return collect($specialties)
            ->map(function (Specialty $specialty, int $index) use ($doctors) {
                $service = Service::query()->updateOrCreate(
                    ['name' => 'Consulta '.$specialty->name],
                    [
                        'description' => 'Consulta de '.$specialty->name,
                        'price' => 40 + ($index * 10),
                        'duration_minutes' => 30 + (($index % 2) * 15),
                        'status' => 'activo',
                    ],
                );

                $service->specialties()->syncWithoutDetaching([$specialty->specialty_id]);

                $doctorIds = collect($doctors)
                    ->where('specialty_id', $specialty->specialty_id)
                    ->pluck('doctor_id')
                    ->all();

                if (! empty($doctorIds)) {
                    $service->doctors()->syncWithoutDetaching($doctorIds);
                }

                return $service->load('specialties');
            });
    }

    private function patients(): array
    {
        $patients = [];
        $names = [
            ['Lucia', 'Ramirez Torres'],
            ['Mateo', 'Vargas Salazar'],
            ['Valentina', 'Rojas Medina'],
            ['Santiago', 'Castillo Flores'],
            ['Camila', 'Mendoza Quispe'],
            ['Sebastian', 'Paredes Leon'],
            ['Isabella', 'Nunez Aguilar'],
            ['Nicolas', 'Herrera Campos'],
            ['Sofia', 'Cabrera Rios'],
            ['Daniel', 'Morales Vega'],
            ['Antonella', 'Silva Cardenas'],
            ['Gabriel', 'Reyes Huaman'],
            ['Martina', 'Ortiz Delgado'],
            ['Adrian', 'Guzman Soto'],
            ['Maria', 'Chavez Luna'],
            ['Joaquin', 'Navarro Pena'],
            ['Fernanda', 'Ibarra Castro'],
            ['Diego', 'Molina Ponce'],
            ['Ximena', 'Salas Bravo'],
            ['Leonardo', 'Acosta Valdez'],
            ['Renata', 'Fuentes Mejia'],
            ['Emiliano', 'Benavides Cruz'],
            ['Ariana', 'Miranda Arias'],
            ['Rodrigo', 'Espinoza Vera'],
            ['Paula', 'Carranza Gil'],
            ['Thiago', 'Sanchez Prado'],
            ['Daniela', 'Vega Calderon'],
            ['Alonso', 'Campos Ruiz'],
            ['Victoria', 'Lagos Mena'],
            ['Bruno', 'Palacios Rivas'],
            ['Catalina', 'Torres Lozano'],
            ['Hugo', 'Escobar Pineda'],
            ['Ana', 'Marquez Solis'],
            ['Marco', 'Aguirre Roman'],
            ['Regina', 'Cortez Vidal'],
            ['Pablo', 'Zuniga Ochoa'],
            ['Claudia', 'Ramos Tapia'],
            ['Luis', 'Valencia Robles'],
            ['Elena', 'Bellido Franco'],
            ['Miguel', 'Aranda Cueva'],
            ['Carolina', 'Montoya Saenz'],
            ['Andres', 'Linares Paz'],
        ];

        for ($index = 1; $index <= 42; $index++) {
            [$firstName, $lastName] = $names[$index - 1];
            $createdAt = Carbon::today()->subDays(($index * 5) % 90)->setTime(9 + ($index % 8), 0);
            $patient = Patient::query()->updateOrCreate(
                ['document_number' => 'RPT-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT)],
                [
                    'document_type' => 'DNI',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'birth_date' => Carbon::today()->subYears(20 + ($index % 45))->toDateString(),
                    'gender' => $index % 2 === 0 ? 'female' : 'male',
                    'phone' => '999'.str_pad((string) $index, 6, '0', STR_PAD_LEFT),
                    'email' => strtolower($firstName).'.'.strtolower(str_replace(' ', '.', $lastName)).'@santalucia.test',
                    'address' => 'Direccion demo '.$index,
                    'insurance_type' => $index % 3 === 0 ? 'Privado' : 'Particular',
                    'status' => 'activo',
                ],
            );
            $patient->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->save();

            $patients[] = $patient;
        }

        return $patients;
    }
}
