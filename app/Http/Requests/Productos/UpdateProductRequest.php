<?php

namespace App\Http\Requests\Productos;
use Illuminate\Foundation\Http\FormRequest;
class UpdateProductRequest extends FormRequest{
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
            'details' => ['nullable', 'string', 'max:500'],
            'idAlmacen' => ['required', 'exists:almacens,id'],
            'state' => ['required', 'boolean'],
            // Foto opcional (solo si el usuario sube una nueva)
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }
    public function messages(): array{
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no debe superar los 100 caracteres.',
            'idCategory.required' => 'La categoría es obligatoria.',
            'idCategory.exists' => 'La categoría seleccionada no existe.',
            'priceSale.required' => 'El precio de venta es obligatorio.',
            'priceSale.numeric' => 'El precio de venta debe ser un número.',
            'priceSale.min' => 'El precio de venta no puede ser negativo.',
            'quantityUnitMeasure.required' => 'La cantidad de medida es obligatoria.',
            'quantityUnitMeasure.numeric' => 'La cantidad de medida debe ser un número.',
            'quantityUnitMeasure.min' => 'La cantidad de medida no puede ser negativa.',
            'unitMeasure.required' => 'La unidad de medida es obligatoria.',
            'unitMeasure.string' => 'La unidad de medida debe ser texto.',
            'unitMeasure.max' => 'La unidad de medida no debe exceder los 100 caracteres.',
            'details.max' => 'Los detalles no deben superar los 500 caracteres.',
            'idAlmacen.required' => 'El almacén es obligatorio.',
            'idAlmacen.exists' => 'El almacén seleccionado no existe.',
            'state.required' => 'El estado es obligatorio.',
            'state.boolean' => 'El estado debe ser verdadero o falso.',
            'foto.image' => 'La foto debe ser una imagen.',
            'foto.mimes' => 'La foto debe estar en formato jpg, jpeg o png.',
            'foto.max' => 'La foto no debe exceder los 5 MB.',
        ];
    }
}
