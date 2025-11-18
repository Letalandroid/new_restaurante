<?php

namespace App\Http\Requests\Reservaciones;

use Illuminate\Foundation\Http\FormRequest;

class StoreLReservationRequest extends FormRequest
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
            // Campos del cliente
            'name' => 'required|string|max:150',
            'lastname' => 'required|string|max:150',
            'email' => 'required|email',
            'phone' => 'required|digits:9',
            'codigo' => ['required', 'regex:/^\d{8}$|^\d{11}$/'],
            'client_type_id' => 'required|exists:client_types,id',
            
            // Campos de la reservación
            'number_people' => 'required|integer|min:1',
            'date' => 'required|date|after_or_equal:today',
            'hour' => 'required|date_format:H:i',
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
            
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder los 150 caracteres.',

            'lastname.required' => 'El apellido es obligatorio.',
            'lastname.string' => 'El apellido debe ser una cadena de texto.',
            'lastname.max' => 'El apellido no debe exceder los 150 caracteres.',

            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección de correo válida.',

            'phone.required' => 'El teléfono es obligatorio.',
            'phone.digits' => 'El teléfono debe contener exactamente 9 dígitos.',

            'codigo.required' => 'El código es obligatorio.',
            'codigo.regex' => 'El código debe contener exactamente 8/11 números (DNI/RUC).',

            'client_type_id.required' => 'El tipo de cliente es obligatorio.',
            'client_type_id.exists' => 'El tipo de cliente seleccionado no es válido.',

            'number_people.required' => 'El número de personas es obligatorio.',
            'number_people.integer' => 'El número de personas debe ser un número entero.',
            'number_people.min' => 'El número de personas debe ser al menos 1.',

            'date.required' => 'La fecha es obligatoria.',
            'date.date' => 'La fecha debe ser una fecha válida.',
            'date.after_or_equal' => 'La fecha debe ser igual o posterior a hoy.',

            'hour.required' => 'La hora es obligatoria.',
            'hour.date_format' => 'El formato de la hora debe ser HH:MM.',
        ];
    }
}