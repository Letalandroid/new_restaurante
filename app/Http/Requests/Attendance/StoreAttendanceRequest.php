<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
public function rules(): array
{
    return [
        'employee_id' => 'required|integer|exists:employees,id',
        'work_date' => 'required|date',
        'check_in' => 'required|date_format:H:i',
        'status_id' => 'required|integer|exists:attendance_statuses,id',
        'justification' => 'nullable|string|max:255',
    ];
}

public function messages(): array
{
    return [
        'employee_id.required' => 'El empleado es obligatorio.',
        'work_date.required' => 'La fecha es obligatoria.',
        'check_in.required' => 'La hora de entrada es obligatoria.',
        'check_in.date_format' => 'La hora de entrada debe tener el formato HH:mm.',
        'status_id.required' => 'El estado es obligatorio.',
        'status_id.exists' => 'El estado seleccionado no es válido.',
    ];
}

}
