<?php

namespace App\Http\Requests\KardexInput;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMovementInputKardexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper($this->input('code')),  // Convertir el código a mayúsculas
        ]);
    }

    public function rules()
    {
        return [
            'idInput' => 'nullable|integer|required_without:idProduct',
            'idProduct' => 'nullable|integer|required_without:idInput',
            'idMovementInput' => 'required|integer',
            'totalPrice' => 'required|numeric|min:0',
        ];
    }

    // Mensajes personalizados de validación
    public function messages()
    {
        return [
            'idInput.integer' => 'El ID del insumo debe ser un número entero.',
            'idInput.required_without' => 'El ID del insumo es obligatorio si no se proporciona el ID del producto.',
            'idProduct.integer' => 'El ID del producto debe ser un número entero.',
            'idProduct.required_without' => 'El ID del producto es obligatorio si no se proporciona el ID del insumo.',
            'idMovementInput.required' => 'El IdMovementInput del insumo es obligatorio.',
            'idMovementInput.integer' => 'El IdMovementInput del insumo debe ser un número entero.',
            'totalPrice.required' => 'El precio total es obligatorio.',
            'totalPrice.numeric' => 'El precio total debe ser un número.',
            'totalPrice.min' => 'El precio total debe ser mayor o igual a 0.',
        ];
    }
    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $data = $this->all();
        
        if (!empty($data['idInput']) && !empty($data['idProduct'])) {
            $validator->errors()->add('idInput', 'No puede especificar tanto insumo como producto.');
        }
        
        if (empty($data['idInput']) && empty($data['idProduct'])) {
            $validator->errors()->add('idInput', 'Debe especificar un insumo o un producto.');
        }
    });
}
}
