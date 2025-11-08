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
            'employee_id'   => 'required|integer|exists:employees,id',
            'work_date'     => 'required|date',

            // 🔹 La hora de entrada es requerida excepto si el estado es 3 (FALTA) o 5 (DÍA LIBRE)
            'check_in'      => 'nullable|date_format:H:i|required_unless:status_id,3,5',

            'status_id'     => 'required|integer|exists:attendance_statuses,id',
            'justification' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'El empleado es obligatorio.',
            'work_date.required'   => 'La fecha es obligatoria.',

            'check_in.required_unless' => 'La hora de entrada es obligatoria salvo que el estado sea FALTA o DÍA LIBRE.',
            'check_in.date_format'     => 'La hora de entrada debe tener el formato HH:mm.',

            'status_id.required' => 'El estado es obligatorio.',
            'status_id.exists'   => 'El estado seleccionado no es válido.',
        ];
    }
}
