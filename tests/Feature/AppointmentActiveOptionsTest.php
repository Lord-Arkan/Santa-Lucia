<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AppointmentActiveOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_appointment_only_exposes_active_available_entities(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'module_permissions' => ['appointments'],
        ]);
        $specialty = Specialty::query()->create(['name' => 'Medicina']);

        $activeDoctor = $this->doctor($specialty, 'Doctor Activo', true, 'activo');
        $inactiveDoctor = $this->doctor($specialty, 'Doctor Inactivo', true, 'inactivo');
        $doctorWithoutActiveSchedule = $this->doctor($specialty, 'Doctor Sin Horario', true, 'activo', 'inactivo');
        $disabledUserDoctor = $this->doctor($specialty, 'Doctor Usuario Inactivo', false, 'activo');

        $activePatient = $this->patient('10000001', 'activo');
        $inactivePatient = $this->patient('10000002', 'inactivo');

        $activeService = Service::query()->create(['name' => 'Servicio Activo', 'duration_minutes' => 30, 'status' => 'activo']);
        $inactiveService = Service::query()->create(['name' => 'Servicio Inactivo', 'duration_minutes' => 30, 'status' => 'inactivo']);
        $unavailableService = Service::query()->create(['name' => 'Servicio Sin Doctor Activo', 'duration_minutes' => 30, 'status' => 'activo']);
        $unavailableService->doctors()->attach($inactiveDoctor->doctor_id);

        $this->actingAs($admin)
            ->get(route('appointments.create'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('doctors', fn ($doctors) => collect($doctors)->pluck('doctor_id')->all() === [$activeDoctor->doctor_id])
                ->where('patients', fn ($patients) => collect($patients)->pluck('patient_id')->all() === [$activePatient->patient_id])
                ->where('services', fn ($services) => collect($services)->pluck('service_id')->all() === [$activeService->service_id]));

        $this->assertNotSame($inactivePatient->patient_id, $activePatient->patient_id);
        $this->assertNotSame($inactiveService->service_id, $activeService->service_id);
        $this->assertNotSame($doctorWithoutActiveSchedule->doctor_id, $disabledUserDoctor->doctor_id);
    }

    public function test_inactive_entities_cannot_be_used_by_manual_appointment_request(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'module_permissions' => ['appointments'],
        ]);
        $specialty = Specialty::query()->create(['name' => 'Medicina']);
        $doctor = $this->doctor($specialty, 'Doctor Activo', true, 'activo');
        $inactiveDoctor = $this->doctor($specialty, 'Doctor Inactivo', true, 'inactivo');
        $patient = $this->patient('10000001', 'activo');
        $inactivePatient = $this->patient('10000002', 'inactivo');
        $activeService = Service::query()->create(['name' => 'Servicio Activo', 'duration_minutes' => 30, 'status' => 'activo']);
        $inactiveService = Service::query()->create(['name' => 'Servicio Inactivo', 'duration_minutes' => 30, 'status' => 'inactivo']);

        $basePayload = [
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'service_id' => $activeService->service_id,
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'start_time' => '09:00',
        ];

        foreach ([
            ['field' => 'patient_id', 'value' => $inactivePatient->patient_id],
            ['field' => 'doctor_id', 'value' => $inactiveDoctor->doctor_id],
            ['field' => 'service_id', 'value' => $inactiveService->service_id],
        ] as $invalidEntity) {
            $this->actingAs($admin)
                ->post(route('appointments.store'), [
                    ...$basePayload,
                    $invalidEntity['field'] => $invalidEntity['value'],
                ])
                ->assertSessionHasErrors($invalidEntity['field']);
        }

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_inactive_service_or_schedule_returns_no_slots(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'module_permissions' => ['appointments'],
        ]);
        $specialty = Specialty::query()->create(['name' => 'Medicina']);
        $doctor = $this->doctor($specialty, 'Doctor Activo', true, 'activo', 'inactivo');
        $activeService = Service::query()->create(['name' => 'Servicio Activo', 'duration_minutes' => 30, 'status' => 'activo']);
        $inactiveService = Service::query()->create(['name' => 'Servicio Inactivo', 'duration_minutes' => 30, 'status' => 'inactivo']);

        $params = [
            'doctor_id' => $doctor->doctor_id,
            'appointment_date' => Carbon::tomorrow()->toDateString(),
        ];

        $this->actingAs($admin)
            ->getJson(route('appointments.slots', [...$params, 'service_id' => $activeService->service_id]))
            ->assertExactJson(['slots' => []]);

        $this->actingAs($admin)
            ->getJson(route('appointments.slots', [...$params, 'service_id' => $inactiveService->service_id]))
            ->assertExactJson(['slots' => []]);
    }

    public function test_patient_created_from_appointments_is_always_active(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'module_permissions' => ['appointments'],
        ]);

        $this->actingAs($admin)
            ->postJson(route('appointments.patients.store'), [
                'document_type' => 'DNI',
                'document_number' => '10000003',
                'first_name' => 'Paciente',
                'last_name' => 'Nuevo',
                'status' => 'inactivo',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('patients', [
            'document_number' => '10000003',
            'status' => 'activo',
        ]);
    }

    private function doctor(Specialty $specialty, string $name, bool $userStatus, string $doctorStatus, string $scheduleStatus = 'activo'): Doctor
    {
        $user = User::factory()->create([
            'name' => $name,
            'rol' => 'doctor',
            'status' => $userStatus,
        ]);
        $doctor = Doctor::query()->create([
            'user_id' => $user->id,
            'specialty_id' => $specialty->specialty_id,
            'license_number' => 'CMP-'.$user->id,
            'status' => $doctorStatus,
        ]);

        DoctorSchedule::query()->create([
            'doctor_id' => $doctor->doctor_id,
            'day_of_week' => strtoupper(Carbon::tomorrow()->format('l')),
            'start_time' => '08:00',
            'end_time' => '12:00',
            'status' => $scheduleStatus,
        ]);

        return $doctor;
    }

    private function patient(string $document, string $status): Patient
    {
        return Patient::query()->create([
            'document_type' => 'DNI',
            'document_number' => $document,
            'first_name' => 'Paciente',
            'last_name' => $document,
            'status' => $status,
        ]);
    }
}
