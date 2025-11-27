<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajusta según tu política
    }

    public function rules(): array
    {
        return [
            'idCustomer'=>'required|exists:customers,id',
            'idUser' => 'required|exists:users,id',
            'idTable' => 'required|exists:tables,id', // Asegura que la mesa exista
            'state'=>'string|max:255',

            // -----------------------------------------------------
            // VALIDACIÓN FLEXIBLE DE PLATOS
            // -----------------------------------------------------
            'platos' => 'nullable|array',
            'platos.*.id' => 'required_with:platos|exists:dishes,id',
            'platos.*.cantidad' => 'required_with:platos|integer|min:1',
            'platos.*.price' => 'required_with:platos|numeric|min:0',

            // -----------------------------------------------------
            // VALIDACIÓN FLEXIBLE DE PRODUCTOS
            // -----------------------------------------------------
            'productos' => 'nullable|array',
            'productos.*.id' => 'required_with:productos|exists:products,id',
            'productos.*.cantidad' => 'required_with:productos|integer|min:1',
            'productos.*.price' => 'required_with:productos|numeric|min:0',

            // -----------------------------------------------------
            // AL MENOS UNO ES OBLIGATORIO
            // -----------------------------------------------------
            'platos' => 'required_without:productos|array',
            'productos' => 'required_without:platos|array',
        ];
    }
}