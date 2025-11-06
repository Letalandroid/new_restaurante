<?php

namespace App\Http\Requests\Tipo_Empleado;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtolower($this->input('name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'payment_type' => 'required|in:fijo,por_hora',
            'base_salary' => 'nullable|numeric',
            'hourly_rate' => 'nullable|numeric',
            'has_punctuality_bonus' => 'required|boolean',
            'punctuality_bonus' => 'nullable|numeric',
            'state' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 100 caracteres.',

            'payment_type.required' => 'El tipo de pago es obligatorio.',
            'payment_type.in' => 'El tipo de pago debe ser "fijo" o "por_hora".',

            'base_salary.numeric' => 'El salario base debe ser un número.',
            'hourly_rate.numeric' => 'La tarifa por hora debe ser un número.',

            'has_punctuality_bonus.required' => 'Indicar si tiene bonificación es obligatorio.',
            'has_punctuality_bonus.boolean' => 'El valor debe ser verdadero o falso.',

            'punctuality_bonus.numeric' => 'La bonificación por puntualidad debe ser un número.',

            'state.required' => 'El estado es obligatorio.',
            'state.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }
}
