<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PatientAccessService
{
    public function doctorId(User $user): ?int
    {
        if ($user->rol !== 'doctor') {
            return null;
        }

        return Doctor::query()->where('user_id', $user->id)->value('doctor_id');
    }

    public function constrainPatients(Builder $query, User $user): Builder
    {
        $doctorId = $this->doctorId($user);

        if ($user->rol === 'doctor') {
            $query->whereHas('appointments', fn (Builder $appointments) => $appointments->where('doctor_id', $doctorId ?? 0));
        }

        return $query;
    }

    public function constrainAppointments(Builder $query, User $user): Builder
    {
        $doctorId = $this->doctorId($user);

        if ($user->rol === 'doctor') {
            $query->where('doctor_id', $doctorId ?? 0);
        }

        return $query;
    }

    public function canAccessPatient(User $user, Patient $patient): bool
    {
        if ($user->rol !== 'doctor') {
            return true;
        }

        return $patient->appointments()
            ->where('doctor_id', $this->doctorId($user) ?? 0)
            ->exists();
    }

    public function canAccessAppointment(User $user, Appointment $appointment): bool
    {
        if ($user->rol !== 'doctor') {
            return true;
        }

        return $appointment->doctor_id === $this->doctorId($user)
            || $this->canAccessPatient($user, $appointment->patient);
    }

    public function canManageAppointment(User $user, Appointment $appointment): bool
    {
        return $user->rol !== 'doctor'
            || $appointment->doctor_id === $this->doctorId($user);
    }
}
