<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id'     => 'required|exists:employees,id',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'base_salary'     => 'required|numeric|min:0',
            'hours_worked'    => 'nullable|numeric|min:0',
            'overtime_hours'  => 'nullable|numeric|min:0',
            'overtime_payment'=> 'nullable|numeric|min:0',
            'bonuses'         => 'nullable|numeric|min:0',
            'deductions'      => 'nullable|numeric|min:0',
            'gross_total'     => 'nullable|numeric|min:0',
            'afp_discount'    => 'nullable|numeric|min:0',
            'net_total'       => 'nullable|numeric|min:0',
            'paid'            => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'El empleado es obligatorio.',
            'employee_id.exists'   => 'El empleado seleccionado no existe.',

            'start_date.required'  => 'La fecha de inicio es obligatoria.',
            'start_date.date'      => 'La fecha de inicio debe ser válida.',

            'end_date.required'    => 'La fecha de fin es obligatoria.',
            'end_date.date'        => 'La fecha de fin debe ser válida.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',

            'base_salary.required' => 'El sueldo base es obligatorio.',
            'base_salary.numeric'  => 'El sueldo base debe ser un número.',
            'base_salary.min'      => 'El sueldo base no puede ser negativo.',

            'hours_worked.numeric' => 'Las horas trabajadas deben ser un número.',
            'overtime_hours.numeric' => 'Las horas extra deben ser un número.',
            'overtime_payment.numeric' => 'El pago por horas extra debe ser un número.',
            'bonuses.numeric'      => 'Los bonos deben ser un número.',
            'deductions.numeric'   => 'Los descuentos deben ser un número.',
            'gross_total.numeric'  => 'El total bruto debe ser un número.',
            'afp_discount.numeric' => 'El descuento AFP debe ser un número.',
            'net_total.numeric'    => 'El total neto debe ser un número.',
            'paid.boolean'         => 'El estado de pago debe ser verdadero o falso.',
        ];
    }
}
