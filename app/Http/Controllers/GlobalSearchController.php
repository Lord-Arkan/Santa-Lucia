<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\PatientAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __construct(private readonly PatientAccessService $access)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $user = $request->user();

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = collect();

        if ($user->hasModuleAccess('patients') || $user->hasModuleAccess('history')) {
            $patients = $this->access->constrainPatients(Patient::query(), $user)
                ->where(function ($builder) use ($query) {
                    $builder->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%")
                        ->orWhere('document_number', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get()
                ->map(fn (Patient $patient) => [
                    'type' => 'Paciente',
                    'title' => trim($patient->first_name.' '.$patient->last_name),
                    'subtitle' => $patient->document_type.' '.$patient->document_number,
                    'url' => route('patients.history', $patient),
                ]);

            $results = $results->concat($patients);
        }

        if ($user->hasModuleAccess('doctors')) {
            $doctors = Doctor::query()
                ->with(['user', 'specialty'])
                ->whereHas('user', fn ($builder) => $builder->where('name', 'like', "%{$query}%"))
                ->limit(5)
                ->get()
                ->map(fn (Doctor $doctor) => [
                    'type' => 'Doctor',
                    'title' => $doctor->user?->name,
                    'subtitle' => $doctor->specialty?->name ?? $doctor->license_number,
                    'url' => route('doctors.show', $doctor),
                ]);

            $results = $results->concat($doctors);
        }

        if ($user->hasModuleAccess('appointments')) {
            $appointments = $this->access->constrainAppointments(
                Appointment::query()->with(['patient', 'doctor.user', 'service']),
                $user,
            )
                ->where(function ($builder) use ($query) {
                    $builder->whereHas('patient', function ($patients) use ($query) {
                        $patients->where('first_name', 'like', "%{$query}%")
                            ->orWhere('last_name', 'like', "%{$query}%")
                            ->orWhere('document_number', 'like', "%{$query}%");
                    })->orWhereHas('doctor.user', fn ($users) => $users->where('name', 'like', "%{$query}%"))
                        ->orWhereHas('service', fn ($services) => $services->where('name', 'like', "%{$query}%"));
                })
                ->latest('appointment_date')
                ->limit(5)
                ->get()
                ->map(fn (Appointment $appointment) => [
                    'type' => 'Cita',
                    'title' => trim($appointment->patient?->first_name.' '.$appointment->patient?->last_name),
                    'subtitle' => $appointment->appointment_date?->format('d/m/Y').' '.$appointment->start_time?->format('H:i').' - '.$appointment->doctor?->user?->name,
                    'url' => route('appointments.show', $appointment),
                ]);

            $results = $results->concat($appointments);
        }

        return response()->json(['results' => $results->values()]);
    }
}
