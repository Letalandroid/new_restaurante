<?php

namespace App\Http\Requests\Reservaciones;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number_people' => 'required|integer|min:1',
            'date' => 'required|date|after_or_equal:today',
            'hour' => 'required|date_format:H:i',
            'state' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'number_people.required' => 'El número de personas es obligatorio.',
            'number_people.integer' => 'El número de personas debe ser un número entero.',
            'number_people.min' => 'El número de personas debe ser al menos 1.',

            'date.required' => 'La fecha es obligatoria.',
            'date.date' => 'La fecha debe ser una fecha válida.',
            'date.after_or_equal' => 'La fecha debe ser igual o posterior a hoy.',

            'hour.required' => 'La hora es obligatoria.',
            'hour.date_format' => 'El formato de la hora debe ser HH:MM.',

            'state.required' => 'El estado es obligatorio.',
            'state.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }
}