<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        $specialtyId = $this->route('specialty')?->specialty_id ?? null;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('specialties', 'name')->ignore($specialtyId, 'specialty_id')],
            'status' => ['nullable', Rule::in(['activo', 'inactivo'])],
        ];
    }
}
