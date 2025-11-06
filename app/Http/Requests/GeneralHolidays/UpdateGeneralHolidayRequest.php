<?php

namespace App\Http\Requests\GeneralHolidays;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $holidayId = optional($this->route('generalHoliday'))->id;

        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:general_holidays,date,' . $holidayId,
            'is_recurring' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del feriado es obligatorio.',
            'date.required' => 'La fecha es obligatoria.',
            'date.unique' => 'Ya existe un feriado en esa fecha.',
            'is_recurring.boolean' => 'El campo "recurrente" debe ser verdadero o falso.',
        ];
    }
}
