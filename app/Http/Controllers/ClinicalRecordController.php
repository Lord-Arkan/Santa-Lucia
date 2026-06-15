<?php

namespace App\Http\Controllers;

use App\Models\ClinicalRecord;
use App\Models\Patient;
use App\Services\PatientAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClinicalRecordController extends Controller
{
    public function __construct(private readonly PatientAccessService $access)
    {
    }

    public function index(Request $request, Patient $patient): Response
    {
        abort_unless($this->access->canAccessPatient($request->user(), $patient), 403);

        $records = ClinicalRecord::query()
            ->with('doctor.user')
            ->where('patient_id', $patient->patient_id)
            ->latest()
            ->get()
            ->map(fn (ClinicalRecord $record) => [
                'id' => $record->id,
                'doctor' => $record->doctor?->user?->name,
                'type' => $record->type,
                'content' => $record->content,
                'created_at' => $record->created_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Patients/Records', [
            'patient' => $this->patientData($patient),
            'records' => $records,
        ]);
    }

    public function store(Request $request, Patient $patient): RedirectResponse
    {
        abort_unless($request->user()->rol === 'doctor', 403);
        abort_unless($this->access->canAccessPatient($request->user(), $patient), 403);

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:10000'],
        ]);

        $doctorId = $this->access->doctorId($request->user());
        abort_if(! $doctorId, 422, 'No se encontro el registro de doctor para este usuario.');

        ClinicalRecord::query()->create([
            ...$validated,
            'patient_id' => $patient->patient_id,
            'doctor_id' => $doctorId,
        ]);

        return back()->with('status', 'Registro clinico agregado correctamente.');
    }

    private function patientData(Patient $patient): array
    {
        return [
            'patient_id' => $patient->patient_id,
            'name' => trim($patient->first_name.' '.$patient->last_name),
            'document' => $patient->document_type.' '.$patient->document_number,
        ];
    }
}
