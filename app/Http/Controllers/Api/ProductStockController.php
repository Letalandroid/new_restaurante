<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\KardexInput;
use App\Models\MovementInputDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductStockController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todos los productos que tengan stock
        $productsWithStock = Product::select('products.id', 'products.name', 'products.quantityUnitMeasure', 'products.unitMeasure')
            ->leftJoin('detail_movements_inputs', 'products.id', '=', 'detail_movements_inputs.idProduct')
            ->leftJoin('kardex_inputs', 'detail_movements_inputs.idMovementInput', '=', 'kardex_inputs.idMovementInput')
            ->selectRaw('SUM(CASE WHEN kardex_inputs.movement_type = 0 THEN detail_movements_inputs.quantity
                        WHEN kardex_inputs.movement_type = 1 THEN -detail_movements_inputs.quantity
                        ELSE 0 END) AS stock')
            ->groupBy('products.id', 'products.name', 'products.quantityUnitMeasure', 'products.unitMeasure')
            ->havingRaw('SUM(CASE WHEN kardex_inputs.movement_type = 0 THEN detail_movements_inputs.quantity
                        WHEN kardex_inputs.movement_type = 1 THEN -detail_movements_inputs.quantity
                        ELSE 0 END) > 0')
            ->get();

        // Devolver los datos en la respuesta
        return response()->json([
            'state' => true,
            'message' => 'Lista de productos con stock disponible',
            'products' => $productsWithStock
        ]);
    }
}
