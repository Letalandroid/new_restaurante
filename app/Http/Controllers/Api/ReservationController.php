<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservaciones\StoreLReservationRequest;
use App\Http\Requests\Reservaciones\StoreReservationRequest;
use App\Http\Requests\Reservaciones\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Customer;
use App\Models\Reservation;
use App\Pipelines\FilterByDate;
use App\Pipelines\FilterByReservationCode;
use App\Pipelines\FilterByCustomerName;
use App\Pipelines\FilterByName;
use App\Pipelines\FilterByState;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Reservation::class);
        $perPage = $request->input('per_page', 15);
        $search = $request->input(key: 'search');
        //$date = $request->input('date');
        //$reservationCode = $request->input('reservation_code');

        $query = app(Pipeline::class)
            ->send(Reservation::query()->with('customer'))
            ->through([
                 new FilterByName($search), //AUN FALTA PIPELINES DE BUSCAR POR NOMBRE, POR FECHA, POR CODIGO DE RESERVACION
                    //Y POR ESTADO
                //new FilterByDate($date),
                //new FilterByReservationCode($reservationCode),
                new FilterByState($request->input('state')),
            ])
            ->thenReturn();

        return ReservationResource::collection($query->paginate($perPage));
    }

    public function storeLanding(StoreLReservationRequest $request)
    {
        
        $validated = $request->validated();

        // Usar updateOrCreate para evitar el problema de secuencia
        $customer = Customer::updateOrCreate(
            ['codigo' => $validated['codigo']],
            [
                'name' => $validated['name'],
                'lastname' => $validated['lastname'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'client_type_id' => $validated['client_type_id'],
                'state' => true, // Por defecto activo
            ]
        );

        // Crear la reservación
        $reservation = Reservation::create([
            'customer_id' => $customer->id,
            'number_people' => $validated['number_people'],
            'date' => $validated['date'],
            'hour' => $validated['hour'],
            'reservation_code' => $this->generateReservationCode(),
            'state' => true, // ← AGREGAR ESTA LÍNEA
        ]);

        return response()->json([
            'state' => true,
            'message' => 'Reservación creada correctamente.',
            'reservation' => new ReservationResource($reservation->load('customer'))
        ]);
    }
    public function store(StoreReservationRequest $request)
    {
        Gate::authorize('create', Reservation::class);
        $validated = $request->validated();
        $reservation = Reservation::create($validated);
        return response()->json([
            'state' => true,
            'message' => 'Reservacion registrada correctamente.',
            'reservation' => $reservation
        ]);
    }

    public function show(Reservation $reservation)
    {
        Gate::authorize('view', $reservation);
        return response()->json([
            'status' => true,
            'message' => 'Reservación encontrada',
            'reservation' => new ReservationResource($reservation->load('customer'))
        ]);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        Gate::authorize('update', $reservation);
        $validated = $request->validated();
        
        $reservation->update($validated);

        return response()->json([
            'state' => true,
            'message' => 'Reservación actualizada correctamente.',
            'reservation' => $reservation->refresh()
        ]);
    }

    public function destroy(Reservation $reservation)
    {
        Gate::authorize('delete', $reservation);
        $reservation->delete();
        
        return response()->json([
            'state' => true,
            'message' => 'Reservación eliminada correctamente'
        ]);
    }

    /**
     * Genera un código de reservación único con letras y números
     */
    private function generateReservationCode(): string
    {
        do {
            // Generar código de 6 caracteres con letras y números
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while (Reservation::where('reservation_code', $code)->exists());

        return $code;
    }
}