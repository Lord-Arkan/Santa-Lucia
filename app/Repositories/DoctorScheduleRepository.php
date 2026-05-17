<?php

namespace App\Repositories;

use App\Models\DoctorSchedule;
use Illuminate\Support\Facades\DB;

class DoctorScheduleRepository
{
    public function listarHorariosPorDoctor(int $doctorId)
    {
        return DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('status', 'activo')
            ->orderBy('day_of_week', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();
    }

    public function listarTodos()
    {
        return DoctorSchedule::query()->with('doctor.user')->latest()->paginate(10);
    }

    public function registrarHorario(array $data): DoctorSchedule
    {
        return DoctorSchedule::query()->create($data);
    }

    public function actualizarHorario(int $scheduleId, array $data): bool
    {
        $schedule = DoctorSchedule::query()->findOrFail($scheduleId);

        return $schedule->update($data);
    }

    public function eliminarHorario(int $scheduleId): bool
    {
        $schedule = DoctorSchedule::query()->findOrFail($scheduleId);
        $schedule->status = 'inactivo';

        return $schedule->save();
    }

    public function existeSolapamiento(int $doctorId, string $dayOfWeek, string $startTime, string $endTime, int $excludeScheduleId = null): bool
    {
        $query = DB::table('doctor_schedules')
            ->where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('status', 'activo');

        if ($excludeScheduleId) {
            $query->where('schedule_id', '<>', $excludeScheduleId);
        }

        $query->where(function ($q) use ($startTime, $endTime) {
            $q->where('start_time', '<', $endTime)
              ->where('end_time', '>', $startTime);
        });

        return $query->exists();
    }
}
