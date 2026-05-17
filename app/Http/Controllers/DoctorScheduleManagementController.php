<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorScheduleRequest;
use App\Http\Requests\UpdateDoctorScheduleRequest;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
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

        $schedules->getCollection()->transform(fn (DoctorSchedule $s) => [
            'schedule_id' => $s->schedule_id,
            'doctor_id' => $s->doctor_id,
            'doctor_name' => $s->doctor?->user?->name,
            'day_of_week' => $s->day_of_week,
            'start_time' => substr($s->start_time, 0, 5),
            'end_time' => substr($s->end_time, 0, 5),
            'status' => $s->status,
            'created_at' => $s->created_at?->format('d/m/Y'),
        ]);

        return Inertia::render('DoctorSchedules/Index', [
            'doctor_schedules' => $schedules,
            'filters' => $request->only(['doctor_id']),
            'doctors' => Doctor::query()->with('user')->get()->map(fn ($d) => ['doctor_id' => $d->doctor_id, 'name' => $d->user?->name]),
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

        $this->repo->eliminarHorario($schedule->schedule_id);

        return back()->with('status', 'Horario inactivado correctamente.');
    }
}
