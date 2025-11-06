<?php

namespace App\Http\Requests\GeneralHolidays;

use Illuminate\Foundation\Http\FormRequest;

class StoreGeneralHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // o usa Gates si tienes roles
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:general_holidays,date',
            'is_recurring' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del feriado es obligatorio.',
            'date.required' => 'La fecha es obligatoria.',
            'date.unique' => 'Ya existe un feriado en esa fecha.',
        ];
    }
}
