<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->rol, ['administrador', 'jefe_area'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $patientId = $this->route('patient')?->patient_id;

        return [
            'document_type' => ['required', 'string', 'max:50'],
            'document_number' => ['required', 'string', 'max:255', Rule::unique('patients', 'document_number')->ignore($patientId, 'patient_id')],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('patients', 'email')->ignore($patientId, 'patient_id')],
            'address' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'string'],
            'previous_conditions' => ['nullable', 'string'],
            'insurance_type' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['activo', 'inactivo'])],
        ];
    }
}
