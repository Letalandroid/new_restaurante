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
            'employee_id'   => 'sometimes|required|integer|exists:employees,id',
            'work_date'     => 'sometimes|required|date',

            'check_in'  => 'nullable|date_format:H:i|date_format:H:i:s|required_unless:status_id,3,5',
'check_out' => 'nullable|date_format:H:i|date_format:H:i:s|after:check_in|required_unless:status_id,3,5',

            'status_id'     => 'required|integer|in:1,2,3,4,5',
            'justification' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'check_in.required_unless'  => 'La hora de entrada es obligatoria salvo que el estado sea FALTA o DÍA LIBRE.',
            'check_out.required_unless' => 'La hora de salida es obligatoria salvo que el estado sea FALTA o DÍA LIBRE.',
            'check_out.after'           => 'La hora de salida debe ser después de la hora de entrada.',
            'status_id.required'        => 'El estado es obligatorio.',
            'status_id.in'              => 'El estado seleccionado no es válido.',
            'work_date.required'        => 'La fecha es obligatoria.',
        ];
    }
}
