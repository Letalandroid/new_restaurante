<?php

namespace App\Http\Requests\Productos;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest{
    public function authorize(): bool{
        return true;
    }
    public function rules(): array{
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'idCategory' => ['required', 'exists:categories,id'],
            'priceSale' => 'required|numeric|min:0',
            'quantityUnitMeasure' => 'required|numeric|min:0',
            'unitMeasure' => 'required|string|max:100',
            'stock' => 'required|integer|min:1|max:1000000',
            'details' => ['nullable', 'string', 'max:500'],
            'idAlmacen' => ['required', 'exists:almacens,id'],
            'state' => ['required', 'boolean'],
            // La foto ahora es opcional
            'foto'  => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }
    public function messages(): array{
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no debe superar los 100 caracteres.',
            'idCategory.required' => 'La categoría es obligatoria.',
            'idCategory.exists' => 'La categoría seleccionada no existe.',
            'details.max' => 'Los detalles no deben superar los 500 caracteres.',
            'idAlmacen.required' => 'El almacén es obligatorio.',
            'idAlmacen.exists' => 'El almacén seleccionado no existe.',
            'state.required' => 'El estado es obligatorio.',
            'state.boolean' => 'El estado debe ser verdadero o falso.',
            'priceSale.required' => 'El precio de venta es obligatorio.',
            'priceSale.numeric' => 'El precio de venta debe ser un número.',
            'priceSale.min' => 'El precio de venta no puede ser negativo.',
            'quantityUnitMeasure.required' => 'La cantidad de medida es obligatoria.',
            'quantityUnitMeasure.numeric' => 'La cantidad de medida debe ser un número.',
            'quantityUnitMeasure.min' => 'La cantidad de medida no puede ser negativa.',
            'unitMeasure.required' => 'La unidad de medida es obligatoria.',
            'unitMeasure.string' => 'La unidad de medida debe ser texto.',
            'unitMeasure.max' => 'La unidad de medida no debe exceder los 100 caracteres.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock no puede ser negativo.',
            'stock.max' => 'El stock no puede exceder 1,000,000.',
            'foto.image' => 'La foto debe ser una imagen.',
            'foto.mimes' => 'La foto debe estar en formato jpg, jpeg o png.',
            'foto.max' => 'La foto no debe exceder los 5 MB.',
        ];
    }
}
