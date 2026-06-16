<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ClinicalAccessAndSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_only_sees_related_patients_and_cannot_open_unrelated_history(): void
    {
        [$doctorUser, $doctor, $related, $unrelated, $service] = $this->clinicalContext();
        $this->appointment($related, $doctor, $service, now()->addDay());

        $this->actingAs($doctorUser)
            ->get(route('patients.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('patients.data', 1)
                ->where('patients.data.0.patient_id', $related->patient_id));

        $this->actingAs($doctorUser)
            ->get(route('patients.history', $unrelated))
            ->assertForbidden();
    }

    public function test_doctor_can_create_traced_clinical_record_for_related_patient(): void
    {
        [$doctorUser, $doctor, $related, , $service] = $this->clinicalContext();
        $this->appointment($related, $doctor, $service, now()->subDay(), 'COMPLETED');

        $this->actingAs($doctorUser)
            ->post(route('patients.records.store', $related), [
                'type' => 'Diagnostico',
                'content' => 'Control clinico estable.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('clinical_records', [
            'patient_id' => $related->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'type' => 'Diagnostico',
        ]);
    }

    public function test_global_search_respects_doctor_patient_relationship(): void
    {
        [$doctorUser, $doctor, $related, $unrelated, $service] = $this->clinicalContext();
        $this->appointment($related, $doctor, $service, now()->addDay());

        $this->actingAs($doctorUser)
            ->getJson(route('global-search', ['q' => 'Paciente']))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Paciente Relacionado'])
            ->assertJsonMissing(['title' => 'Paciente Ajeno'])
            ->assertJsonMissing(['type' => 'Cita'])
            ->assertJsonMissing(['type' => 'Citas'])
            ->assertJsonMissing(['type' => 'Doctor']);
    }

    public function test_global_search_sends_doctor_matches_to_filtered_appointments(): void
    {
        $admin = User::factory()->create([
            'rol' => 'administrador',
            'module_permissions' => ['appointments', 'patients'],
        ]);
        $specialty = Specialty::query()->create(['name' => 'Medicina']);
        $doctorUser = User::factory()->create([
            'name' => 'Doctor Buscado',
            'rol' => 'doctor',
        ]);
        $doctor = Doctor::query()->create([
            'user_id' => $doctorUser->id,
            'specialty_id' => $specialty->specialty_id,
            'license_number' => 'CMP-SEARCH',
        ]);
        $patient = Patient::query()->create([
            'document_type' => 'DNI',
            'document_number' => '20000001',
            'first_name' => 'Paciente',
            'last_name' => 'Con Cita',
        ]);
        $service = Service::query()->create([
            'name' => 'Consulta general',
            'duration_minutes' => 30,
        ]);
        $this->appointment($patient, $doctor, $service, now()->addDay());

        $response = $this->actingAs($admin)
            ->getJson(route('global-search', ['q' => 'Buscado']))
            ->assertOk();

        $results = collect($response->json('results'));

        $this->assertTrue($results->contains(fn (array $result) => $result['type'] === 'Citas'
            && $result['title'] === 'Citas de Doctor Buscado'
            && $result['url'] === route('appointments.index', ['doctor_id' => $doctor->doctor_id])));
        $this->assertFalse($results->contains(fn (array $result) => $result['type'] === 'Doctor'
            || str_contains($result['url'], '/doctors/')));
    }

    public function test_doctor_user_global_search_does_not_find_other_doctors(): void
    {
        [$doctorUser] = $this->clinicalContext();
        $specialty = Specialty::query()->first();
        $otherDoctorUser = User::factory()->create([
            'name' => 'Medico Externo',
            'rol' => 'doctor',
        ]);
        Doctor::query()->create([
            'user_id' => $otherDoctorUser->id,
            'specialty_id' => $specialty->specialty_id,
            'license_number' => 'CMP-OUT',
        ]);

        $this->actingAs($doctorUser)
            ->getJson(route('global-search', ['q' => 'Externo']))
            ->assertOk()
            ->assertExactJson(['results' => []]);
    }

    public function test_dashboard_upcoming_appointments_only_contains_future_scheduled_appointments_from_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-14 11:00:00', 'America/Lima'));

        [$user, $doctor, $patient, , $service] = $this->clinicalContext('administrador');
        $this->appointment($patient, $doctor, $service, now()->setTime(10, 0));
        $this->appointment($patient, $doctor, $service, now()->setTime(12, 0));
        $this->appointment($patient, $doctor, $service, now()->setTime(13, 0), 'CANCELLED');
        $this->appointment($patient, $doctor, $service, now()->addDay()->setTime(9, 0));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('upcomingAppointments', 1)
                ->where('upcomingAppointments.0', '14/06 - 12:00 - Paciente Relacionado'));
    }

    private function clinicalContext(string $role = 'doctor'): array
    {
        $user = User::factory()->create([
            'rol' => $role,
            'module_permissions' => ['dashboard', 'appointments', 'patients', 'history'],
        ]);
        $specialty = Specialty::query()->create(['name' => 'Medicina']);
        $doctor = Doctor::query()->create([
            'user_id' => $user->id,
            'specialty_id' => $specialty->specialty_id,
            'license_number' => 'CMP-001',
        ]);
        $related = Patient::query()->create([
            'document_type' => 'DNI',
            'document_number' => '10000001',
            'first_name' => 'Paciente',
            'last_name' => 'Relacionado',
        ]);
        $unrelated = Patient::query()->create([
            'document_type' => 'DNI',
            'document_number' => '10000002',
            'first_name' => 'Paciente',
            'last_name' => 'Ajeno',
        ]);
        $service = Service::query()->create([
            'name' => 'Consulta general',
            'duration_minutes' => 30,
        ]);

        return [$user, $doctor, $related, $unrelated, $service];
    }

    private function appointment(Patient $patient, Doctor $doctor, Service $service, Carbon $dateTime, string $status = 'SCHEDULED'): Appointment
    {
        return Appointment::query()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctor->doctor_id,
            'service_id' => $service->service_id,
            'appointment_date' => $dateTime->toDateString(),
            'start_time' => $dateTime->format('H:i:s'),
            'end_time' => $dateTime->copy()->addMinutes(30)->format('H:i:s'),
            'status' => $status,
        ]);
    }
}
