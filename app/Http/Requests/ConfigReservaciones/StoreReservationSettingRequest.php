<?php

namespace App\Http\Requests\ConfigReservaciones;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'waiting_minutes' => 'required|integer|min:1|max:60',
            'auto_expire' => 'required|boolean',
        ];
    }
    public function messages(): array
    {
        return [
            'waiting_minutes.required' => 'Los minutos de espera son obligatorios.',
            'waiting_minutes.integer' => 'Los minutos de espera deben ser un número entero.',
            'waiting_minutes.min' => 'Los minutos de espera deben ser al menos 1.',
            'waiting_minutes.max' => 'Los minutos de espera no pueden ser más de 60.',

            'auto_expire.required' => 'El campo expiración automática es obligatorio.',
            'auto_expire.boolean' => 'El campo expiración automática debe ser verdadero o falso.',
        ];
    }
}