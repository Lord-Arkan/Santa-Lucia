<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use App\Models\Appointment;
use App\Services\PatientAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientManagementController extends Controller
{
    public function __construct(private readonly PatientAccessService $access)
    {
    }

    public function index(Request $request): Response
    {
        $query = $this->access->constrainPatients(Patient::query(), $request->user());

        if ($request->filled('first_name')) {
            $query->where('first_name', 'like', '%'.$request->query('first_name').'%');
        }

        if ($request->filled('last_name')) {
            $query->where('last_name', 'like', '%'.$request->query('last_name').'%');
        }

        if ($request->filled('document_number')) {
            $query->where('document_number', 'like', '%'.$request->query('document_number').'%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%'.$request->query('email').'%');
        }

        $patients = $query->latest()->paginate(10);

        $patients->getCollection()->transform(fn (Patient $patient) => [
            'patient_id' => $patient->patient_id,
            'document_type' => $patient->document_type,
            'document_number' => $patient->document_number,
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            // ISO for form usage, human-friendly for display
            'birth_date' => $patient->birth_date?->format('Y-m-d'),
            'birth_date_formatted' => $patient->birth_date?->format('d/m/Y'),
            'gender' => $patient->gender,
            'phone' => $patient->phone,
            'email' => $patient->email,
            'address' => $patient->address,
            'blood_type' => $patient->blood_type,
            'allergies' => $patient->allergies,
            'previous_conditions' => $patient->previous_conditions,
            'insurance_type' => $patient->insurance_type,
            'status' => $patient->status,
            'created_at' => $patient->created_at?->format('d/m/Y'),
        ]);

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'filters' => $request->only(['first_name', 'last_name', 'document_number', 'email']),
            'canManagePatients' => in_array($request->user()->rol, ['administrador', 'jefe_area'], true),
            'canAddClinicalRecords' => $request->user()->rol === 'doctor',
        ]);
    }

    public function history(Request $request, Patient $patient): Response
    {
        abort_unless($this->access->canAccessPatient($request->user(), $patient), 403);

        $appointments = Appointment::query()
            ->with(['doctor.user', 'service'])
            ->where('patient_id', $patient->patient_id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->get()
            ->map(fn (Appointment $appointment) => [
                'appointment_id' => $appointment->appointment_id,
                'doctor' => $appointment->doctor?->user?->name,
                'service' => $appointment->service?->name,
                'appointment_date' => $appointment->appointment_date?->format('d/m/Y'),
                'start_time' => $appointment->start_time?->format('H:i'),
                'status' => $appointment->status,
                'detail_url' => $request->user()->hasModuleAccess('appointments')
                    ? route('appointments.show', $appointment)
                    : null,
            ]);

        return Inertia::render('Patients/History', [
            'patient' => [
                'patient_id' => $patient->patient_id,
                'name' => trim($patient->first_name.' '.$patient->last_name),
                'document' => $patient->document_type.' '.$patient->document_number,
                'allergies' => $patient->allergies,
                'previous_conditions' => $patient->previous_conditions,
            ],
            'appointments' => $appointments,
            'clinicalRecords' => $patient->clinicalRecords()
                ->with('doctor.user')
                ->latest()
                ->get()
                ->map(fn ($record) => [
                    'id' => $record->id,
                    'doctor' => $record->doctor?->user?->name,
                    'type' => $record->type,
                    'content' => $record->content,
                    'created_at' => $record->created_at?->format('d/m/Y H:i'),
                ]),
        ]);
    }

    public function historyIndex(Request $request): Response
    {
        $query = $this->access->constrainPatients(Patient::query(), $request->user())
            ->withCount(['appointments', 'clinicalRecords']);

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('patient_id', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $patients = $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        $patients->getCollection()->transform(fn (Patient $patient) => [
                'patient_id' => $patient->patient_id,
                'name' => trim($patient->first_name.' '.$patient->last_name),
                'document' => $patient->document_type.' '.$patient->document_number,
                'status' => $patient->status,
                'appointments_count' => $patient->appointments_count,
                'clinical_records_count' => $patient->clinical_records_count,
            ]);

        return Inertia::render('Patients/HistoryIndex', [
            'patients' => $patients,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Patient::query()->create($validated);

        return back()->with('status', 'Paciente creado correctamente.');
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validated();

        $patient->update($validated);

        return back()->with('status', 'Paciente actualizado correctamente.');
    }

    public function toggleStatus(Request $request, Patient $patient): RedirectResponse
    {
        abort_unless(in_array($request->user()->rol, ['administrador', 'jefe_area'], true), 403);

        $patient->status = $patient->status === 'activo' ? 'inactivo' : 'activo';
        $patient->save();

        $message = $patient->status === 'activo' ? 'Paciente habilitado correctamente.' : 'Paciente inhabilitado correctamente.';

        return back()->with('status', $message);
    }

    public function destroy(Request $request, Patient $patient): RedirectResponse
    {
        abort_unless(in_array($request->user()->rol, ['administrador', 'jefe_area'], true), 403);

        Patient::destroy($patient->patient_id);

        return back()->with('status', 'Paciente eliminado correctamente.');
    }
}
