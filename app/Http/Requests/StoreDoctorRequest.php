<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id', 'unique:doctors,user_id'],
            'specialty_id' => ['required', 'exists:specialties,specialty_id'],
            'license_number' => ['required', 'string'],
            'status' => ['nullable', 'in:activo,inactivo'],
        ];
    }
}
