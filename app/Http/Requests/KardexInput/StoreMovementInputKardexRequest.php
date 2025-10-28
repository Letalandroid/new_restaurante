<?php

namespace App\Http\Requests\KardexInput;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovementInputKardexRequest extends FormRequest
{
    public function authorize()
    {
        // Permitir la solicitud solo si el usuario tiene autorización
        return true;
    }

  public function rules()
{
    return [
        'idUser' => 'required|integer',
        'idInput' => 'nullable|integer|required_without:idProduct',
        'idProduct' => 'nullable|integer|required_without:idInput',
        'idMovementInput' => 'nullable|integer', // Permitir nulo
        'movement_type' => 'required|integer|in:0,1', // 0 para INGRESO, 1 para SALIDA
        'totalPrice' => 'nullable|numeric|min:0', // Permitir nulo
    ];
}

    // Agregar mensajes personalizados para cada regla de validación
    public function messages()
    {
        return [
            'idUser.required' => 'El ID del usuario es obligatorio.',
            'idUser.integer' => 'El ID del usuario debe ser un número entero.',
            
            'idInput.integer' => 'El ID del insumo debe ser un número entero.',
            'idInput.required_without' => 'El ID del insumo es obligatorio si no se proporciona el ID del producto.',

            'idProduct.integer' => 'El ID del producto debe ser un número entero.',
            'idProduct.required_without' => 'El ID del producto es obligatorio si no se proporciona el ID del insumo.',
            
            'idMovementInput.required' => 'El ID del movimiento de insumo es obligatorio.',
            'idMovementInput.integer' => 'El ID del movimiento de insumo debe ser un número entero.',
            
            'movement_type.required' => 'El tipo de movimiento es obligatorio.',
            'movement_type.integer' => 'El tipo de movimiento debe ser un número entero.',
            'movement_type.in' => 'El tipo de movimiento debe ser uno de los siguientes: 0 (INGRESO), 1 (SALIDA).',
          
            
            'totalPrice.required' => 'El precio total es obligatorio.',
            'totalPrice.numeric' => 'El precio total debe ser un número.',
            'totalPrice.min' => 'El precio total debe ser mayor o igual a 0.',
        ];
    }
    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $data = $this->all();
        
        // Validar que no se envíen ambos
        if (!empty($data['idInput']) && !empty($data['idProduct'])) {
            $validator->errors()->add('idInput', 'No puede especificar tanto insumo como producto.');
        }
        
        // Validar que al menos uno esté presente
        if (empty($data['idInput']) && empty($data['idProduct'])) {
            $validator->errors()->add('idInput', 'Debe especificar un insumo o un producto.');
        }
    });
}
}
