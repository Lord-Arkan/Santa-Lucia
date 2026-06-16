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

class ReportsAndSpecialtiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_configuration_user_can_manage_specialties_and_duplicate_names_are_rejected(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'module_permissions' => ['configuration'],
        ]);

        $this->actingAs($admin)
            ->post(route('specialties.store'), [
                'name' => 'Cardiologia',
                'status' => 'activo',
            ])
            ->assertSessionHasNoErrors();

        $specialty = Specialty::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('specialties.store'), [
                'name' => 'Cardiologia',
                'status' => 'activo',
            ])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)
            ->put(route('specialties.update', $specialty), [
                'name' => 'Medicina Interna',
                'status' => 'activo',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->patch(route('specialties.toggleStatus', $specialty))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('specialties', [
            'specialty_id' => $specialty->specialty_id,
            'name' => 'Medicina Interna',
            'status' => 'inactivo',
        ]);
    }

    public function test_user_without_configuration_permission_cannot_open_specialties(): void
    {
        $assistant = User::factory()->create([
            'rol' => 'asistente',
            'module_permissions' => ['dashboard'],
        ]);

        $this->actingAs($assistant)
            ->get(route('specialties.index'))
            ->assertForbidden();
    }

    public function test_reports_dashboard_counts_appointments_and_exports_csv(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'module_permissions' => ['reports'],
        ]);
        [$doctor, $patient, $service] = $this->clinicalContext();

        $this->appointment($patient, $doctor, $service, '2026-06-01', 'SCHEDULED');
        $this->appointment($patient, $doctor, $service, '2026-06-02', 'COMPLETED');
        $this->appointment($patient, $doctor, $service, '2026-06-03', 'CANCELLED');

        $this->actingAs($admin)
            ->get(route('reports.index', [
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
                'group_by' => 'day',
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('summary.total_appointments', 3)
                ->where('summary.pending_appointments', 1)
                ->where('summary.attended_appointments', 1)
                ->where('summary.cancelled_appointments', 1)
                ->has('periodReport.rows.data', 3)
                ->has('doctorReport.rows.data', 1)
                ->has('specialtyReport.rows.data', 1));

        $this->actingAs($admin)
            ->get(route('reports.export', [
                'format' => 'csv',
                'section' => 'period',
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-30',
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    private function clinicalContext(): array
    {
        $specialty = Specialty::query()->create(['name' => 'Medicina General']);
        $doctorUser = User::factory()->create([
            'name' => 'Doctor Reporte',
            'rol' => 'doctor',
        ]);
        $doctor = Doctor::query()->create([
            'user_id' => $doctorUser->id,
            'specialty_id' => $specialty->specialty_id,
            'license_number' => 'CMP-RPT',
        ]);
        $patient = Patient::query()->create([
            'document_type' => 'DNI',
            'document_number' => '70000001',
            'first_name' => 'Paciente',
            'last_name' => 'Reporte',
        ]);
        $service = Service::query()->create([
            'name' => 'Consulta',
            'duration_minutes' => 30,
        ]);

        return [$doctor, $patient, $service];
    }

    private function appointment(Patient $patient, Doctor $doctor, Service $service, string $date, string $status): Appointment
    {
        return Appointment::query()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'service_id' => $service->service_id,
            'appointment_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => $status,
        ]);
    }
}
