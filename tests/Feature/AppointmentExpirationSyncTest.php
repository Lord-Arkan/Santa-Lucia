<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AppointmentExpirationSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sqlite_sync_marks_past_appointments_as_expired_before_appointments_index(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'module_permissions' => ['appointments'],
        ]);

        $appointment = $this->createPastScheduledAppointment();

        $this->actingAs($admin)
            ->get(route('appointments.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('appointments.data.0.status', 'EXPIRED')
                ->where('appointments.data.0.status_label', 'Vencida'));

        $this->assertDatabaseHas('appointments', [
            'appointment_id' => $appointment->appointment_id,
            'status' => 'EXPIRED',
        ]);
    }

    public function test_sqlite_sync_marks_past_appointments_as_expired_before_reports_index(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'module_permissions' => ['reports'],
        ]);

        $appointment = $this->createPastScheduledAppointment();

        $this->actingAs($admin)
            ->get(route('reports.index', [
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('summary.pending_appointments', 0)
                ->where('summary.total_appointments', 1)
                ->where('summary.attended_appointments', 0)
                ->where('summary.cancelled_appointments', 0));

        $this->assertDatabaseHas('appointments', [
            'appointment_id' => $appointment->appointment_id,
            'status' => 'EXPIRED',
        ]);
    }

    private function createPastScheduledAppointment(): Appointment
    {
        $specialty = Specialty::query()->create(['name' => 'Medicina General']);
        $doctorUser = User::factory()->create([
            'name' => 'Doctor Expiracion',
            'rol' => 'doctor',
        ]);
        $doctor = Doctor::query()->create([
            'user_id' => $doctorUser->id,
            'specialty_id' => $specialty->specialty_id,
            'license_number' => 'CMP-EXP-001',
        ]);
        $patient = Patient::query()->create([
            'document_type' => 'DNI',
            'document_number' => '90000001',
            'first_name' => 'Paciente',
            'last_name' => 'Pasado',
        ]);
        $service = Service::query()->create([
            'name' => 'Consulta',
            'duration_minutes' => 30,
        ]);

        return Appointment::query()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'service_id' => $service->service_id,
            'appointment_date' => now()->subDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'SCHEDULED',
        ]);
    }
}
