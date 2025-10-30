<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_out' => 'required|date_format:H:i|after:check_in',
        ];
    }

    public function messages(): array
    {
        return [
            'check_out.required' => 'La hora de salida es obligatoria.',
            'check_out.date_format' => 'La hora de salida debe tener el formato HH:mm.',
            'check_out.after' => 'La hora de salida debe ser después de la hora de entrada.',
        ];
    }
}
