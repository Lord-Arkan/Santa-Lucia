<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class AppointmentRepository
{
    public function listarCitas()
    {
        return Appointment::query()->with(['patient', 'doctor.user', 'service'])->latest()->paginate(10);
    }

    public function listarPorDoctor(int $doctorId)
    {
        return Appointment::query()->with(['patient', 'service'])->where('doctor_id', $doctorId)->latest()->paginate(10);
    }

    public function listarPorPaciente(int $patientId)
    {
        return Appointment::query()->with(['doctor.user', 'service'])->where('patient_id', $patientId)->latest()->paginate(10);
    }

    public function registrarCita(array $data): Appointment
    {
        return Appointment::query()->create($data);
    }

    public function actualizarEstado(int $appointmentId, string $status): bool
    {
        $a = Appointment::query()->findOrFail($appointmentId);
        $a->status = $status;

        return $a->save();
    }

    public function cancelarCita(int $appointmentId): bool
    {
        $a = Appointment::query()->findOrFail($appointmentId);
        $a->status = 'CANCELLED';

        return $a->save();
    }

    public function existeSolapamiento(int $doctorId, string $appointmentDate, string $startTime, string $endTime, int $excludeAppointmentId = null): bool
    {
        $query = DB::table('appointments')
            ->where('doctor_id', $doctorId)
            ->where('appointment_date', $appointmentDate)
            ->where('status', 'SCHEDULED');

        if ($excludeAppointmentId) {
            $query->where('appointment_id', '<>', $excludeAppointmentId);
        }

        $query->where(function ($q) use ($startTime, $endTime) {
            $q->where('start_time', '<', $endTime)
              ->where('end_time', '>', $startTime);
        });

        return $query->exists();
    }
}
