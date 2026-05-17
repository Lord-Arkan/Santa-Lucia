<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Models\Patient;
use Illuminate\Validation\Rule;
use App\Repositories\AppointmentRepository;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentManagementController extends Controller
{
    public function __construct(protected AppointmentRepository $repo)
    {
    }

    public function index(Request $request): Response
    {
        $query = Appointment::query()->with(['patient', 'doctor.user', 'service']);

        $user = $request->user();

        if ($user && $user->rol === 'doctor') {
            $doctorId = Doctor::query()->where('user_id', $user->id)->value('doctor_id');
            $query->where('doctor_id', $doctorId);
        } else {
            if ($request->filled('doctor_id')) {
                $query->where('doctor_id', $request->query('doctor_id'));
            }

            if ($request->filled('patient_id')) {
                $query->where('patient_id', $request->query('patient_id'));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('appointment_date')) {
            $query->where('appointment_date', $request->query('appointment_date'));
        }

        $appointments = $query->latest()->paginate(10);

        $appointments->getCollection()->transform(function (Appointment $a) {
            $statusMap = [
                'SCHEDULED' => 'Programada',
                'COMPLETED' => 'Completada',
                'CANCELLED' => 'Cancelada',
                'NO_SHOW' => 'No asistió',
            ];

            $formatTime = function ($t) {
                if ($t instanceof \DateTimeInterface) {
                    return $t->format('H:i');
                }

                if (is_string($t)) {
                    return strlen($t) >= 5 ? substr($t, 0, 5) : $t;
                }

                return null;
            };

            return [
                'appointment_id' => $a->appointment_id,
                'patient' => $a->patient ? ['patient_id' => $a->patient->patient_id, 'name' => ($a->patient->first_name . ' ' . $a->patient->last_name)] : null,
                'doctor' => $a->doctor?->user ? ['doctor_id' => $a->doctor->doctor_id, 'name' => $a->doctor->user->name] : null,
                'service' => $a->service ? ['service_id' => $a->service->service_id, 'name' => $a->service->name] : null,
                'appointment_date' => $a->appointment_date?->format('d/m/Y'),
                'start_time' => $formatTime($a->start_time),
                'end_time' => $formatTime($a->end_time),
                'status' => $a->status,
                'status_label' => $statusMap[$a->status] ?? $a->status,
                'created_at' => $a->created_at?->format('d/m/Y'),
            ];
        });

        $myDoctor = null;
        if ($user && $user->rol === 'doctor') {
            $doc = Doctor::query()->with('user')->where('user_id', $user->id)->first();
            if ($doc) {
                $myDoctor = ['doctor_id' => $doc->doctor_id, 'name' => $doc->user?->name];
            }
        }

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
            'filters' => $request->only(['doctor_id', 'patient_id', 'status', 'appointment_date']),
            'doctors' => Doctor::query()->with('user')->get()->map(fn ($d) => ['doctor_id' => $d->doctor_id, 'name' => $d->user?->name]),
            'patients' => Patient::query()->get()->map(fn ($p) => ['patient_id' => $p->patient_id, 'name' => ($p->first_name . ' ' . $p->last_name)]),
            'services' => Service::query()->with('specialties','doctors')->get()->map(fn ($s) => [
                'service_id' => $s->service_id,
                'name' => $s->name,
                'duration_minutes' => $s->duration_minutes,
                'specialty_ids' => $s->specialties->pluck('specialty_id')->toArray(),
                'doctor_ids' => $s->doctors->pluck('doctor_id')->toArray(),
            ]),
            'specialties' => \App\Models\Specialty::query()->where('status','activo')->get()->map(fn($sp) => ['specialty_id' => $sp->specialty_id, 'name' => $sp->name]),
            'my_doctor' => $myDoctor,
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();

        $myDoctor = null;
        if ($user && $user->rol === 'doctor') {
            $doc = Doctor::query()->with('user')->where('user_id', $user->id)->first();
            if ($doc) {
                $myDoctor = ['doctor_id' => $doc->doctor_id, 'name' => $doc->user?->name];
            }
        }

        return Inertia::render('Appointments/Create', [
            'doctors' => Doctor::query()->with('user')->get()->map(fn ($d) => ['doctor_id' => $d->doctor_id, 'name' => $d->user?->name, 'specialty_id' => $d->specialty_id]),
            'patients' => Patient::query()->get()->map(fn ($p) => ['patient_id' => $p->patient_id, 'name' => ($p->first_name . ' ' . $p->last_name)]),
            'services' => Service::query()->with('specialties','doctors')->get()->map(fn ($s) => [
                'service_id' => $s->service_id,
                'name' => $s->name,
                'duration_minutes' => $s->duration_minutes,
                'specialty_ids' => $s->specialties->pluck('specialty_id')->toArray(),
                'doctor_ids' => $s->doctors->pluck('doctor_id')->toArray(),
            ]),
            'specialties' => \App\Models\Specialty::query()->where('status','activo')->get()->map(fn($sp) => ['specialty_id' => $sp->specialty_id, 'name' => $sp->name]),
            'my_doctor' => $myDoctor,
        ]);
    }

    public function storePatient(Request $request)
    {
        $data = $request->validate([
            'document_type' => ['required', 'string', 'max:50'],
            'document_number' => ['required', 'string', 'max:255', 'unique:patients,document_number'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', 'unique:patients,email'],
            'address' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string'],
            'previous_conditions' => ['nullable', 'string'],
            'insurance_type' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['activo', 'inactivo'])],
        ]);

        $patient = Patient::query()->create(array_merge($data, ['status' => $data['status'] ?? 'activo']));

        return response()->json(['patient' => ['patient_id' => $patient->patient_id, 'name' => $patient->first_name . ' ' . $patient->last_name]], 201);
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // If doctor, force doctor_id
        if ($user && $user->rol === 'doctor') {
            $doctorId = Doctor::query()->where('user_id', $user->id)->value('doctor_id');
            if (! $doctorId) {
                return back()->withErrors(['doctor_id' => 'No se encontró el registro de doctor para este usuario.']);
            }

            $validated['doctor_id'] = $doctorId;
        }

        // Calculate end_time based on service duration
        $service = Service::query()->find($validated['service_id']);
        $duration = $service ? (int) $service->duration_minutes : 30;

        $start = Carbon::createFromFormat('H:i', $validated['start_time']);
        $end = $start->copy()->addMinutes($duration);

        if ($start >= $end) {
            return back()->withErrors(['start_time' => 'La hora de inicio debe ser anterior a la hora de fin.']);
        }

        // Check doctor schedule availability for that day
        $dayOfWeek = strtoupper(Carbon::parse($validated['appointment_date'])->format('l'));

        $fits = DoctorSchedule::query()
            ->where('doctor_id', $validated['doctor_id'])
            ->where('day_of_week', $dayOfWeek)
            ->where('status', 'activo')
            ->where('start_time', '<=', $validated['start_time'])
            ->where('end_time', '>=', $end->format('H:i'))
            ->exists();

        if (! $fits) {
            return back()->withErrors(['start_time' => 'El doctor no tiene disponibilidad en ese horario.']);
        }

        // Check overlap with existing appointments
        if ($this->repo->existeSolapamiento($validated['doctor_id'], $validated['appointment_date'], $validated['start_time'], $end->format('H:i'))) {
            return back()->withErrors(['start_time' => 'El horario seleccionado se solapa con otra cita.']);
        }

        $data = array_merge($validated, ['end_time' => $end->format('H:i'), 'status' => 'SCHEDULED']);

        $this->repo->registrarCita($data);

        return back()->with('status', 'Cita creada correctamente.');
    }

    public function slots(Request $request)
    {
        $doctorId = $request->query('doctor_id');
        $date = $request->query('appointment_date');
        $serviceId = $request->query('service_id');
        if (! $date || ! $serviceId) {
            return response()->json(['slots' => []]);
        }

        $service = Service::query()->with('doctors')->find($serviceId);
        $duration = $service ? (int) $service->duration_minutes : 30;

        $dayOfWeek = strtoupper(Carbon::parse($date)->format('l'));
        $dateIso = Carbon::parse($date)->format('Y-m-d');

        // Resolve which doctors to consider
        if ($doctorId !== null && $doctorId !== '') {
            $doctorIds = [(int) $doctorId];
        } elseif ($service) {
            $doctorIds = $service->doctors->pluck('doctor_id')->toArray();
        } else {
            $doctorIds = [];
        }

        if (empty($doctorIds)) {
            return response()->json(['slots' => []]);
        }

        // Load schedules for those doctors on that day
        $schedulesQuery = DoctorSchedule::query()->where('day_of_week', $dayOfWeek)->where('status', 'activo');
        if (count($doctorIds) === 1) {
            $schedulesQuery->where('doctor_id', $doctorIds[0]);
        } else {
            $schedulesQuery->whereIn('doctor_id', (array) $doctorIds);
        }

        $schedules = $schedulesQuery->get();

        // Load existing appointments grouped by doctor to check overlaps per-doctor
        $appointmentsQuery = Appointment::query()->where('appointment_date', $dateIso)->where('status', 'SCHEDULED');
        if (count($doctorIds) === 1) {
            $appointmentsQuery->where('doctor_id', $doctorIds[0]);
        } else {
            $appointmentsQuery->whereIn('doctor_id', (array) $doctorIds);
        }

        $appointments = $appointmentsQuery->get();

        $appointmentsByDoctor = $appointments->groupBy('doctor_id')->map(function ($group) use ($dateIso) {
            return $group->map(function ($a) use ($dateIso) {
                $start = null;
                $end = null;

                if ($a->start_time instanceof \DateTimeInterface) {
                    $start = $a->start_time->format('H:i');
                } elseif (is_string($a->start_time)) {
                    $start = strlen($a->start_time) >= 5 ? substr($a->start_time, 0, 5) : $a->start_time;
                }

                if ($a->end_time instanceof \DateTimeInterface) {
                    $end = $a->end_time->format('H:i');
                } elseif (is_string($a->end_time)) {
                    $end = strlen($a->end_time) >= 5 ? substr($a->end_time, 0, 5) : $a->end_time;
                }

                if (! $start || ! $end) return null;

                return [
                    'start' => Carbon::createFromFormat('Y-m-d H:i', $dateIso . ' ' . $start),
                    'end' => Carbon::createFromFormat('Y-m-d H:i', $dateIso . ' ' . $end),
                ];
            })->filter()->values();
        });

        $slots = [];

        foreach ($schedules as $s) {
            $docId = $s->doctor_id;

            $sStart = null;
            $sEnd = null;
            if ($s->start_time instanceof \DateTimeInterface) {
                $sStart = $s->start_time->format('H:i');
            } elseif (is_string($s->start_time)) {
                $sStart = strlen($s->start_time) >= 5 ? substr($s->start_time, 0, 5) : $s->start_time;
            }

            if ($s->end_time instanceof \DateTimeInterface) {
                $sEnd = $s->end_time->format('H:i');
            } elseif (is_string($s->end_time)) {
                $sEnd = strlen($s->end_time) >= 5 ? substr($s->end_time, 0, 5) : $s->end_time;
            }

            if (! $sStart || ! $sEnd) continue;

            $current = Carbon::createFromFormat('Y-m-d H:i', $dateIso . ' ' . $sStart);
            $blockEnd = Carbon::createFromFormat('Y-m-d H:i', $dateIso . ' ' . $sEnd);

            $existingForDoctor = $appointmentsByDoctor->get($docId, collect());

            while ($current->copy()->addMinutes($duration) <= $blockEnd) {
                $slotStart = $current->copy();
                $slotEnd = $current->copy()->addMinutes($duration);

                $overlap = false;
                foreach ($existingForDoctor as $e) {
                    if ($e['start']->lt($slotEnd) && $e['end']->gt($slotStart)) {
                        $overlap = true;
                        break;
                    }
                }

                if (! $overlap) {
                    $slots[] = $slotStart->format('H:i');
                }

                $current->addMinutes($duration);
            }
        }

        // Remove duplicates and sort
        $slots = array_values(array_unique($slots));
        sort($slots);

        return response()->json(['slots' => $slots]);
    }

    public function updateStatus(UpdateAppointmentStatusRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->repo->actualizarEstado($appointment->appointment_id, $request->validated()['status']);

        return back()->with('status', 'Estado de cita actualizado.');
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->repo->cancelarCita($appointment->appointment_id);

        return back()->with('status', 'Cita cancelada correctamente.');
    }
}
