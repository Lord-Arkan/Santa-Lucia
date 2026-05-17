<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191', 'unique:services,name'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:activo,inactivo'],
            'specialty_ids' => ['required', 'array', 'min:1'],
            'specialty_ids.*' => ['required', 'exists:specialties,specialty_id'],
            'doctor_ids' => ['nullable', 'array'],
            'doctor_ids.*' => ['nullable', 'exists:doctors,doctor_id'],
        ];
    }
}
