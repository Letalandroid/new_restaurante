<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfigReservaciones\StoreReservationSettingRequest;
use App\Http\Resources\ConfigReservacionResource;
use App\Models\ReservationSetting;

class ReservationSettingController extends Controller
{
    /**
     * Muestra la última configuración registrada.
     */
    public function index()
    {
        // Obtener la última configuración creada
        $setting = ReservationSetting::latest()->first();

        if (!$setting) {
            return response()->json([
                'state' => false,
                'message' => 'No hay configuraciones registradas aún.',
            ], 404);
        }

        return response()->json([
            'state' => true,
            'message' => 'Configuración actual obtenida correctamente.',
            'config' => new ConfigReservacionResource($setting),
        ]);
    }

    /**
     * Crea una nueva configuración o actualiza generando un nuevo registro.
     */
    public function store(StoreReservationSettingRequest $request)
    {
        // Validar datos
        $validated = $request->validated();

        // Crear un nuevo registro (sin eliminar ni sobrescribir el anterior)
        $setting = ReservationSetting::create([
            'waiting_minutes' => $validated['waiting_minutes'],
            'auto_expire' => $validated['auto_expire'],
        ]);

        return response()->json([
            'state' => true,
            'message' => 'Configuración registrada correctamente.',
            'config' => new ConfigReservacionResource($setting),
        ]);
    }
}
