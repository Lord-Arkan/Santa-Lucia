<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceId = $this->route('service')?->service_id ?? null;

        return [
            'name' => ['required', 'string', 'max:191', 'unique:services,name,'.($serviceId ?? 'NULL').',service_id'],
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
