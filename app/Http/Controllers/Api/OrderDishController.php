<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDishResource;

use App\Models\OrderDishes;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pipeline\Pipeline;
use App\Pipelines\FilterByNameHistorial;
use App\Pipelines\FilterByStateHistorial;
use App\Http\Requests\OrderDish\UpdateOrdersDishesRequest;
use App\Models\Dishes;
use App\Models\MovementInputDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderDishController extends Controller
{
    // Lista de pedidos con relaciones
public function index(Request $request)
{
    Gate::authorize('viewAny', arguments: Orders::class);

    $perPage = $request->input('per_page', 15);
    $idOrder = $request->input('idOrder');


    $query = OrderDishes::query();

    // Filtro condicional por idOrder
    if (!is_null($idOrder)) {
        $query->where('idOrder', $idOrder);
    }

    // Aplica otros filtros si existen
    $query = app(Pipeline::class)
        ->send($query)
        ->through([
            new FilterByNameHistorial($request->input('search')),
            new FilterByStateHistorial($request->input(key: 'state')),
        ])
        ->thenReturn();

    $orders = $query->paginate($perPage);

    return OrderDishResource::collection($orders);
}
public function update(UpdateOrdersDishesRequest $request, $id)
{
    $orderDish = OrderDishes::findOrFail($id);

    // Obtener el nuevo estado de la solicitud
    $newState = $request->input('state');

    $isDish = $orderDish->idDishes !== null;
    $isProduct = $orderDish->idProduct !== null;

    /*
    |--------------------------------------------------------------------------
    | FLUJO PARA PLATOS (NO TOCAR)
    |--------------------------------------------------------------------------
    */
    if ($isDish) {

        if ($orderDish->state === 'pendiente') {

            if ($newState === 'cancelado') {
                $orderDish->state = 'cancelado';

                $dish = Dishes::find($orderDish->idDishes);
                if ($dish) {
                    $dish->quantity += $orderDish->quantity;
                    $dish->save();
                }

                $orderDish->save();

                return response()->json([
                    'message' => 'Platillo cancelado correctamente.',
                    'data' => new OrderDishResource($orderDish)
                ], 200);
            }

            if ($newState === 'en preparación') {
                $orderDish->state = 'en preparación';
                $orderDish->save();

                return response()->json([
                    'message' => 'Platillo marcado como en preparación.',
                    'data' => new OrderDishResource($orderDish)
                ], 200);
            }
        }

        if ($orderDish->state === 'en preparación' && $newState === 'en entrega') {
            $orderDish->state = 'en entrega';
            $orderDish->save();

            return response()->json([
                'message' => 'Platillo marcado como en entrega.',
                'data' => new OrderDishResource($orderDish)
            ], 200);
        }

        if ($orderDish->state === 'en entrega' && $newState === 'completado') {
            $orderDish->state = 'completado';
            $orderDish->save();

            return response()->json([
                'message' => 'Platillo completado.',
                'data' => new OrderDishResource($orderDish)
            ], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FLUJO PARA PRODUCTOS
    |--------------------------------------------------------------------------
    */
    if ($isProduct) {

        $product = Product::find($orderDish->idProduct);

        if ($orderDish->state === 'pendiente') {

            // cancelar desde pendiente → devolver stock
            if ($newState === 'cancelado') {

                if ($product) {
                }

                $orderDish->state = 'cancelado';
                $orderDish->save();

                return response()->json([
                    'message' => 'Producto cancelado y stock devuelto.',
                    'data' => new OrderDishResource($orderDish)
                ], 200);
            }

            // pendiente → en entrega
            if ($newState === 'en entrega') {
                $orderDish->state = 'en entrega';
                $orderDish->save();

                return response()->json([
                    'message' => 'Producto marcado como en entrega.',
                    'data' => new OrderDishResource($orderDish)
                ], 200);
            }
        }

        // en entrega → completado
        if ($orderDish->state === 'en entrega' && $newState === 'completado') {
            DB::beginTransaction();
            try {
                $product = Product::find($orderDish->idProduct);
                $cantidadRestante = $orderDish->quantity;
                
                // 1. DESCONTAR STOCK POR LOTE (FIFO)
                $lotes = MovementInputDetail::where('idProduct', $orderDish->idProduct)
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                foreach ($lotes as $lote) {
                    if ($cantidadRestante <= 0) break;
                    
                    if ($lote->quantity >= $cantidadRestante) {
                        // 2. REGISTRAR EN KARDEX Y DETAIL_MOVEMENTS_INPUTS
                        for ($i = 0; $i < $cantidadRestante; $i++) {
                            // Crear registro en KardexInput
                            $kardex = \App\Models\KardexInput::create([
                                'idUser' => auth()->id(),
                                'idProduct' => $orderDish->idProduct,
                                'idMovementInput' => null,
                                'totalPrice' => null,
                                'movement_type' => 1,
                            ]);
                            
                            // ✅ CREAR REGISTRO EN DETAIL_MOVEMENTS_INPUTS
                            \App\Models\MovementInputDetail::create([
                                'idMovementInput' => null,
                                'idInput' => null,
                                'idProduct' => $orderDish->idProduct,
                                'quantity' => 1, // 1 unidad por registro
                                'totalPrice' => null,
                                'priceUnit' => null,
                                'batch' => $lote->batch,
                                'expirationDate' => $lote->expirationDate,
                            ]);
                        }
                        
                        $lote->quantity -= $cantidadRestante;
                        $lote->save();
                        $cantidadRestante = 0;
                    } else {
                        // REGISTRAR SALIDA PARA TODO EL LOTE
                        for ($i = 0; $i < $lote->quantity; $i++) {
                            // Crear registro en KardexInput
                            $kardex = \App\Models\KardexInput::create([
                                'idUser' => auth()->id(),
                                'idProduct' => $orderDish->idProduct,
                                'idMovementInput' => null,
                                'totalPrice' => null,
                                'movement_type' => 1,
                            ]);
                            
                            // ✅ CREAR REGISTRO EN DETAIL_MOVEMENTS_INPUTS
                            \App\Models\MovementInputDetail::create([
                                'idMovementInput' => null,
                                'idInput' => null,
                                'idProduct' => $orderDish->idProduct,
                                'quantity' => 1, // 1 unidad por registro
                                'totalPrice' => null,
                                'priceUnit' => null,
                                'batch' => $lote->batch,
                                'expirationDate' => $lote->expirationDate,
                            ]);
                        }
                        
                        $cantidadRestante -= $lote->quantity;
                        $lote->quantity = 0;
                        $lote->save();
                    }
                }
                
                // 3. ACTUALIZAR ESTADO
                $orderDish->state = 'completado';
                $orderDish->save();
                
                DB::commit();
                
                return response()->json([
                    'message' => 'Producto completado y stock descontado.',
                    'data' => new OrderDishResource($orderDish)
                ], 200);
                
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Error al completar el producto.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }

    return response()->json([
        'message' => 'Transición de estado no permitida.',
    ], 400);
}


}
