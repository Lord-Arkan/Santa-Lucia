<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Patient::query();

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
        $patient->status = $patient->status === 'activo' ? 'inactivo' : 'activo';
        $patient->save();

        $message = $patient->status === 'activo' ? 'Paciente habilitado correctamente.' : 'Paciente inhabilitado correctamente.';

        return back()->with('status', $message);
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        Patient::destroy($patient->patient_id);

        return back()->with('status', 'Paciente eliminado correctamente.');
    }
}
