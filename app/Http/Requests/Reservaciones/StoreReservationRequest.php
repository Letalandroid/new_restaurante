<?php

namespace App\Http\Requests\Reservaciones;
use Illuminate\Foundation\Http\FormRequest;
class StoreReservationRequest extends FormRequest{
    public function authorize(): bool{
        return true;
    }
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'number_people' => 'required|integer|min:1',
            'date' => 'required|date|after_or_equal:today',
            'hour' => 'required|date_format:H:i',
            'state' => 'required|boolean',
            'reservation_code' => 'required|string|unique:reservations,reservation_code', // ← AGREGAR ESTA LÍNEA
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'El cliente es obligatorio.',
            'customer_id.exists' => 'El cliente seleccionado no es válido.',

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

            'reservation_code.required' => 'El código de reservación es obligatorio.',
            'reservation_code.string' => 'El código de reservación debe ser una cadena de texto.',
            'reservation_code.unique' => 'El código de reservación ya existe, genere uno nuevo.',
        ];
    }
}
