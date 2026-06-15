<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', Rule::exists('patients', 'patient_id')->where('status', 'activo')],
            'doctor_id' => ['required', Rule::exists('doctors', 'doctor_id')->where('status', 'activo')],
            'service_id' => ['required', Rule::exists('services', 'service_id')->where('status', 'activo')],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.exists' => 'El paciente seleccionado no esta activo.',
            'doctor_id.exists' => 'El doctor seleccionado no esta activo.',
            'service_id.exists' => 'El servicio seleccionado no esta activo.',
        ];
    }
}
