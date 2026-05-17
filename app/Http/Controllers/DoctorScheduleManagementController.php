<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorScheduleRequest;
use App\Http\Requests\UpdateDoctorScheduleRequest;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Repositories\DoctorScheduleRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorScheduleManagementController extends Controller
{
    public function __construct(protected DoctorScheduleRepository $repo)
    {
    }

    private function normalizeDayToEnglish($d): string
    {
        if (is_numeric($d)) {
            $n = (int) $d;
            if ($n >= 0 && $n <= 6) {
                $arr0 = ['SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];
                return $arr0[$n];
            }
            if ($n >= 1 && $n <= 7) {
                $arr1 = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];
                return $arr1[$n - 1];
            }
        }

        $s = strtolower((string) $d);
        // simple accent removal
        $s = str_replace(['á','é','í','ó','ú','ü','ñ','Á','É','Í','Ó','Ú','Ü','Ñ'], ['a','e','i','o','u','u','n','A','E','I','O','U','U','N'], $s);

        if (str_contains($s, 'dom')) return 'SUNDAY';
        if (str_contains($s, 'lun')) return 'MONDAY';
        if (str_contains($s, 'mar')) return 'TUESDAY';
        if (str_contains($s, 'mie') || str_contains($s, 'mier') || str_contains($s, 'wed')) return 'WEDNESDAY';
        if (str_contains($s, 'jue') || str_contains($s, 'thu')) return 'THURSDAY';
        if (str_contains($s, 'vie') || str_contains($s, 'fri')) return 'FRIDAY';
        if (str_contains($s, 'sab')) return 'SATURDAY';

        // english full names
        if (str_contains($s, 'sunday')) return 'SUNDAY';
        if (str_contains($s, 'monday')) return 'MONDAY';
        if (str_contains($s, 'tuesday')) return 'TUESDAY';
        if (str_contains($s, 'wednesday')) return 'WEDNESDAY';
        if (str_contains($s, 'thursday')) return 'THURSDAY';
        if (str_contains($s, 'friday')) return 'FRIDAY';
        if (str_contains($s, 'saturday')) return 'SATURDAY';

        try {
            return strtoupper(Carbon::parse($d)->format('l'));
        } catch (\Throwable $e) {
            return strtoupper((string) $d);
        }
    }

    private function englishDayToSpanish(string $day): string
    {
        $map = [
            'SUNDAY' => 'DOMINGO',
            'MONDAY' => 'LUNES',
            'TUESDAY' => 'MARTES',
            'WEDNESDAY' => 'MIÉRCOLES',
            'THURSDAY' => 'JUEVES',
            'FRIDAY' => 'VIERNES',
            'SATURDAY' => 'SÁBADO',
        ];

        $u = strtoupper((string) $day);

        return $map[$u] ?? $u;
    }

    public function index(Request $request): Response
    {
        $query = DoctorSchedule::query()->with(['doctor.user']);

        $user = $request->user();

        // If doctor role, show only own schedules
        if ($user && $user->rol === 'doctor') {
            $doctorId = Doctor::query()->where('user_id', $user->id)->value('doctor_id');
            $query->where('doctor_id', $doctorId);
        } else {
            // admin can filter by doctor
            if ($request->filled('doctor_id')) {
                $query->where('doctor_id', $request->query('doctor_id'));
            }
        }

        $schedules = $query->latest()->paginate(10);

        // collect doctor ids shown on this page
        $doctorIds = $schedules->getCollection()->pluck('doctor_id')->unique()->values()->toArray();

        // fetch future scheduled appointments for these doctors
        $futureAppointments = collect();
        if (! empty($doctorIds)) {
            $futureAppointments = Appointment::query()
                ->whereIn('doctor_id', $doctorIds)
                ->where('status', 'SCHEDULED')
                ->whereDate('appointment_date', '>=', Carbon::today()->format('Y-m-d'))
                ->get();
        }

        $schedules->getCollection()->transform(function (DoctorSchedule $s) use ($futureAppointments) {
            $schedDay = $this->normalizeDayToEnglish($s->day_of_week);

            $sStart = is_string($s->start_time) ? substr($s->start_time, 0, 5) : ($s->start_time instanceof \DateTimeInterface ? $s->start_time->format('H:i') : null);
            $sEnd = is_string($s->end_time) ? substr($s->end_time, 0, 5) : ($s->end_time instanceof \DateTimeInterface ? $s->end_time->format('H:i') : null);

            $hasPending = $futureAppointments->contains(function ($a) use ($schedDay, $sStart, $sEnd) {
                try {
                    $aDay = strtoupper(Carbon::parse($a->appointment_date)->format('l'));
                } catch (\Throwable $e) {
                    return false;
                }

                if ($aDay !== $schedDay) return false;

                $aStart = $a->start_time instanceof \DateTimeInterface ? $a->start_time->format('H:i') : (is_string($a->start_time) ? substr($a->start_time, 0, 5) : null);
                $aEnd = $a->end_time instanceof \DateTimeInterface ? $a->end_time->format('H:i') : (is_string($a->end_time) ? substr($a->end_time, 0, 5) : null);

                if (! $aStart || ! $aEnd || ! $sStart || ! $sEnd) return false;

                // if any appointment overlaps schedule block -> locked
                return ($aStart < $sEnd) && ($aEnd > $sStart);
            });

            return [
                'schedule_id' => $s->schedule_id,
                'doctor_id' => $s->doctor_id,
                'doctor_name' => $s->doctor?->user?->name,
                'day_of_week' => $this->englishDayToSpanish($schedDay),
                'start_time' => substr($s->start_time, 0, 5),
                'end_time' => substr($s->end_time, 0, 5),
                'status' => $s->status,
                'created_at' => $s->created_at?->format('d/m/Y'),
                'locked' => $hasPending,
            ];
        });

        $myDoctor = null;
        if ($user && $user->rol === 'doctor') {
            $doc = Doctor::query()->with('user')->where('user_id', $user->id)->first();
            if ($doc) {
                $myDoctor = ['doctor_id' => $doc->doctor_id, 'name' => $doc->user?->name];
            }
        }

        return Inertia::render('DoctorSchedules/Index', [
            'doctor_schedules' => $schedules,
            'filters' => $request->only(['doctor_id']),
            'doctors' => Doctor::query()->with('user')->get()->map(fn ($d) => ['doctor_id' => $d->doctor_id, 'name' => $d->user?->name]),
            'my_doctor' => $myDoctor,
        ]);
    }

    public function store(StoreDoctorScheduleRequest $request): RedirectResponse
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

        if (empty($validated['doctor_id'])) {
            return back()->withErrors(['doctor_id' => 'Seleccione un doctor.']);
        }

        // Validate times
        if (! empty($validated['start_time']) && ! empty($validated['end_time']) && $validated['start_time'] >= $validated['end_time']) {
            return back()->withErrors(['start_time' => 'La hora de inicio debe ser anterior a la hora de fin.']);
        }

        // Check overlap
        if ($this->repo->existeSolapamiento($validated['doctor_id'], $validated['day_of_week'], $validated['start_time'], $validated['end_time'])) {
            return back()->withErrors(['start_time' => 'El horario se solapa con otro existente.']);
        }

        $this->repo->registrarHorario($validated);

        return back()->with('status', 'Horario creado correctamente.');
    }

    public function update(UpdateDoctorScheduleRequest $request, DoctorSchedule $schedule): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Access control: doctors can only modify their own schedules
        if ($user && $user->rol === 'doctor') {
            $doctorId = Doctor::query()->where('user_id', $user->id)->value('doctor_id');
            if ($schedule->doctor_id !== $doctorId) {
                abort(403);
            }
            $validated['doctor_id'] = $doctorId;
        }

        // If no doctor_id provided, keep existing
        $doctorId = $validated['doctor_id'] ?? $schedule->doctor_id;

        if ($validated['start_time'] >= $validated['end_time']) {
            return back()->withErrors(['start_time' => 'La hora de inicio debe ser anterior a la hora de fin.']);
        }

        if ($this->repo->existeSolapamiento($doctorId, $validated['day_of_week'], $validated['start_time'], $validated['end_time'], $schedule->schedule_id)) {
            return back()->withErrors(['start_time' => 'El horario se solapa con otro existente.']);
        }

        // Prevent editing if there are future scheduled appointments that overlap this schedule
        $futureAppointments = Appointment::query()
            ->where('doctor_id', $schedule->doctor_id)
            ->where('status', 'SCHEDULED')
            ->whereDate('appointment_date', '>=', Carbon::today()->format('Y-m-d'))
            ->get();

        $schedDay = $this->normalizeDayToEnglish($schedule->day_of_week);
        $sStart = is_string($schedule->start_time) ? substr($schedule->start_time, 0, 5) : ($schedule->start_time instanceof \DateTimeInterface ? $schedule->start_time->format('H:i') : null);
        $sEnd = is_string($schedule->end_time) ? substr($schedule->end_time, 0, 5) : ($schedule->end_time instanceof \DateTimeInterface ? $schedule->end_time->format('H:i') : null);

        $hasPending = $futureAppointments->contains(function ($a) use ($schedDay, $sStart, $sEnd) {
            try {
                $aDay = strtoupper(Carbon::parse($a->appointment_date)->format('l'));
            } catch (\Throwable $e) {
                return false;
            }

            if ($aDay !== $schedDay) return false;

            $aStart = $a->start_time instanceof \DateTimeInterface ? $a->start_time->format('H:i') : (is_string($a->start_time) ? substr($a->start_time, 0, 5) : null);
            $aEnd = $a->end_time instanceof \DateTimeInterface ? $a->end_time->format('H:i') : (is_string($a->end_time) ? substr($a->end_time, 0, 5) : null);

            if (! $aStart || ! $aEnd || ! $sStart || ! $sEnd) return false;

            return ($aStart < $sEnd) && ($aEnd > $sStart);
        });

        if ($hasPending) {
            return back()->withErrors(['start_time' => 'No se puede editar este horario porque hay citas programadas en él.']);
        }

        $this->repo->actualizarHorario($schedule->schedule_id, array_merge($validated, ['doctor_id' => $doctorId]));

        return back()->with('status', 'Horario actualizado correctamente.');
    }

    public function toggleStatus(Request $request, DoctorSchedule $schedule): RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->rol === 'doctor') {
            $doctorId = Doctor::query()->where('user_id', $user->id)->value('doctor_id');
            if ($schedule->doctor_id !== $doctorId) {
                abort(403);
            }
        }

        $schedule->status = $schedule->status === 'activo' ? 'inactivo' : 'activo';
        $schedule->save();

        $message = $schedule->status === 'activo' ? 'Horario habilitado correctamente.' : 'Horario inhabilitado correctamente.';

        return back()->with('status', $message);
    }

    public function destroy(Request $request, DoctorSchedule $schedule): RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->rol === 'doctor') {
            $doctorId = Doctor::query()->where('user_id', $user->id)->value('doctor_id');
            if ($schedule->doctor_id !== $doctorId) {
                abort(403);
            }
        }

        // Prevent deletion if there are future scheduled appointments overlapping this schedule
        $futureAppointments = Appointment::query()
            ->where('doctor_id', $schedule->doctor_id)
            ->where('status', 'SCHEDULED')
            ->whereDate('appointment_date', '>=', Carbon::today()->format('Y-m-d'))
            ->get();

        $schedDay = $this->normalizeDayToEnglish($schedule->day_of_week);
        $sStart = is_string($schedule->start_time) ? substr($schedule->start_time, 0, 5) : ($schedule->start_time instanceof \DateTimeInterface ? $schedule->start_time->format('H:i') : null);
        $sEnd = is_string($schedule->end_time) ? substr($schedule->end_time, 0, 5) : ($schedule->end_time instanceof \DateTimeInterface ? $schedule->end_time->format('H:i') : null);

        $hasPending = $futureAppointments->contains(function ($a) use ($schedDay, $sStart, $sEnd) {
            try {
                $aDay = strtoupper(Carbon::parse($a->appointment_date)->format('l'));
            } catch (\Throwable $e) {
                return false;
            }

            if ($aDay !== $schedDay) return false;

            $aStart = $a->start_time instanceof \DateTimeInterface ? $a->start_time->format('H:i') : (is_string($a->start_time) ? substr($a->start_time, 0, 5) : null);
            $aEnd = $a->end_time instanceof \DateTimeInterface ? $a->end_time->format('H:i') : (is_string($a->end_time) ? substr($a->end_time, 0, 5) : null);

            if (! $aStart || ! $aEnd || ! $sStart || ! $sEnd) return false;

            return ($aStart < $sEnd) && ($aEnd > $sStart);
        });

        if ($hasPending) {
            return back()->withErrors(['start_time' => 'No se puede eliminar este horario porque hay citas programadas en él.']);
        }

        $this->repo->eliminarHorario($schedule->schedule_id);

        return back()->with('status', 'Horario inactivado correctamente.');
    }
}
