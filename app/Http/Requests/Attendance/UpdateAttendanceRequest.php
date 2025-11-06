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
        'check_in' => 'required|date_format:H:i',   // nuevo: permite modificar check_in
        'check_out' => 'required|date_format:H:i|after:check_in',
        'status_id' => 'required|integer|in:1,2,3,4,5', // permite validar estado
        'work_date' => 'required|date', // validación de fecha
    ];
}

public function messages(): array
{
    return [
        'check_in.required' => 'La hora de entrada es obligatoria.',
        'check_in.date_format' => 'La hora de entrada debe tener el formato HH:mm.',
        'check_out.required' => 'La hora de salida es obligatoria.',
        'check_out.date_format' => 'La hora de salida debe tener el formato HH:mm.',
        'check_out.after' => 'La hora de salida debe ser después de la hora de entrada.',
        'status_id.required' => 'El estado es obligatorio.',
        'status_id.in' => 'El estado seleccionado no es válido.',
        'work_date.required' => 'La fecha es obligatoria.',
        'work_date.date' => 'La fecha no es válida.',
    ];
}

}
