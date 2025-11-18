<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KardexInput\StoreMovementInputKardexRequest;
use App\Http\Requests\KardexInput\UpdateMovementInputKardexRequest;
use App\Http\Resources\MovementInputKardexResource;
use App\Models\KardexInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pipeline\Pipeline;
use App\Pipelines\FilterByCodeKardex;
use App\Pipelines\FilterByDateKardex;
use Illuminate\Support\Facades\Log;

class MovementInputKardexController extends Controller
{
public function index(Request $request)
{
    Gate::authorize('viewAny', KardexInput::class);

    $perPage = $request->input('per_page', 15);

    // Inicializar la consulta
    $movementsInput = KardexInput::query();

    // Filtrar por idMovementInput si está presente
    if ($request->has('idMovementInput')) {
        $movementsInput->where('idMovementInput', $request->input('idMovementInput'));
    }

    // Filtrar por AMBOS si está presente
    if ($request->has('idInput')) {
        $movementsInput->where('idInput', $request->input('idInput'));
    }
    
    if ($request->has('idProduct')) {
        $movementsInput->where('idProduct', $request->input('idProduct'));
    }

    // Filtrar por fechas si están presentes
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // Aplicar el pipeline de fechas
    $movementsInput = app(Pipeline::class)
        ->send($movementsInput)
        ->through([
            new FilterByDateKardex($startDate, $endDate),  // Usamos el nuevo pipeline de fechas
            new FilterByCodeKardex($request->input('search')),  // Filtro por código
        ])
        ->thenReturn();

    // Paginar los resultados
    $movementsInput = $movementsInput->paginate($perPage);

    // Devolver la respuesta paginada con los recursos
    return MovementInputKardexResource::collection($movementsInput);
}




  public function store(StoreMovementInputKardexRequest $request)
{
    Gate::authorize('create', KardexInput::class);
    
    // Los datos validados que llegan desde el frontend
    $validated = $request->validated();

    // Crear el nuevo registro de KardexInput
    $InputKardex = KardexInput::create($validated);

    // Retornar una respuesta exitosa
    return response()->json([
        'state' => true,
        'message' => 'Kardex de insumo registrado correctamente.',
        'kardexInput' => $InputKardex
    ]);
}


    public function show(KardexInput $kardexInput)
    {
        Gate::authorize('view', $kardexInput);

        return response()->json([
            'state' => true,
            'message' => 'Kardex Input encontrado',
            'kardexInput' => new MovementInputKardexResource($kardexInput),
        ]);
    }

public function update(UpdateMovementInputKardexRequest $request, KardexInput $kardexInput)
{
    // Obtener los datos validados
    $validated = $request->validated();

    try {
        // Buscar el KardexInput usando los parámetros idInput y idMovementInput
        $query = KardexInput::where('idMovementInput', $validated['idMovementInput']);

        if (isset($validated['idInput'])) {
            $query->where('idInput', $validated['idInput'])
                  ->whereNull('idProduct'); // Asegurar que no sea product
        } elseif (isset($validated['idProduct'])) {
            $query->where('idProduct', $validated['idProduct'])
                  ->whereNull('idInput'); // Asegurar que no sea input
        }

        $kardexInput = $query->first();

        if (!$kardexInput) {
            $logData = isset($validated['idInput']) ? 
                "idInput: {$validated['idInput']}" : 
                "idProduct: {$validated['idProduct']}";
                
            Log::error("No se encontró Kardex con idMovementInput: {$validated['idMovementInput']} y {$logData}");
            return response()->json([
                'state' => false,
                'message' => 'Kardex no encontrado.',
            ], 404);
        }

        // Actualizar el totalPrice del KardexInput
        $kardexInput->totalPrice = $validated['totalPrice'];
        $kardexInput->save();

        // Responder con un mensaje de éxito
        return response()->json([
            'state' => true,
            'message' => 'Kardex actualizado correctamente',
            'kardexInput' => new MovementInputKardexResource($kardexInput),
        ]);
    } catch (\Exception $e) {
        // Registrar cualquier error inesperado
        Log::error('Error al actualizar Kardex: ' . $e->getMessage());
        return response()->json([
            'state' => false,
            'message' => 'Ocurrió un error al actualizar el Kardex.',
        ], 500);
    }
}


    public function destroy(KardexInput $kardexInput)
    {
        Gate::authorize('delete', $kardexInput);

        $kardexInput->delete();

        return response()->json([
            'state' => true,
            'message' => 'Kardex Input eliminado correctamente',
        ]);
    }
}
