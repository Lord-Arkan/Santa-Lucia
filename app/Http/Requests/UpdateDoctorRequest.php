<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $doctorId = $this->route('doctor')?->doctor_id ?? null;

        return [
            'user_id' => ['required', 'exists:users,id', 'unique:doctors,user_id,'.($doctorId ?? 'NULL').',doctor_id'],
            'specialty_id' => ['required', 'exists:specialties,specialty_id'],
            'license_number' => ['required', 'string'],
            'status' => ['nullable', 'in:activo,inactivo'],
        ];
    }
}
