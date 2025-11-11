<?php

namespace App\Http\Controllers\Api;

use App\Exports\ReservationExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reservaciones\StoreLReservationRequest;
use App\Http\Requests\Reservaciones\StoreReservationRequest;
use App\Http\Requests\Reservaciones\UpdateReservationRequest;
use App\Http\Resources\PublicReservationResource;
use App\Http\Resources\ReservationResource;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ReservationSetting;
use App\Pipelines\FilterByCodeRyName;
use App\Pipelines\FilterByDate;
use App\Pipelines\FilterByState;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Reservation::class);
        
        // Primero, actualizar reservaciones expiradas
        $this->expireOldReservations();
        
        $perPage = $request->input('per_page', 15);
        $search = $request->input(key: 'search');
        $date = $request->input('date');

        $query = app(Pipeline::class)
            ->send(Reservation::query()->with('customer'))
            ->through([
                new FilterByCodeRyName($search),
                new FilterByDate($date),
                new FilterByState($request->input('state')),
            ])
            ->thenReturn();

        return ReservationResource::collection($query->paginate($perPage));
    }
    /**
     * Expirar reservaciones pasada la hora de espera y desactivar clientes si es necesario.
     */
    private function expireOldReservations(): void
    {
        //Obtener la última configuración
        $config = ReservationSetting::latest()->first();

        // Si no existe configuración o está desactivada la expiración automática → salir
        if (!$config || !$config->auto_expire) {
            return;
        }

        $now = now()->format('H:i');
        $today = now()->format('Y-m-d');
        
        // Obtener los IDs de las reservaciones que van a expirar
        $expiredReservationIds = Reservation::where('state', true)
            ->where(function($query) use ($today, $now) {
                $query->where('date', '<', $today)
                    ->orWhere(function($q) use ($today, $now) {
                        $q->where('date', $today)
                            ->where('waiting_hour', '<', $now);
                    });
            })
            ->pluck('id');

        if ($expiredReservationIds->isNotEmpty()) {
            // Desactivar las reservaciones
            Reservation::whereIn('id', $expiredReservationIds)->update(['state' => false]);
            
            // Desactivar los clientes asociados a estas reservaciones
            $customerIds = Reservation::whereIn('id', $expiredReservationIds)
                ->pluck('customer_id')
                ->unique()
                ->filter();

            if ($customerIds->isNotEmpty()) {
                Customer::whereIn('id', $customerIds)
                    ->where('state', true)
                    ->update(['state' => false]);
            }
        }
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

        //Obtener configuración dinámica
        $config = ReservationSetting::latest()->first();
        $waitingMinutes = $config?->waiting_minutes ?? 2;

        $hour = Carbon::createFromFormat('H:i', $validated['hour']);
        $waitingHour = $hour->copy()->addMinutes($waitingMinutes)->format('H:i');

        $reservation = Reservation::create([
            'customer_id' => $customer->id,
            'number_people' => $validated['number_people'],
            'date' => $validated['date'],
            'hour' => $validated['hour'],
            'waiting_hour' => $waitingHour,
            'reservation_code' => $this->generateReservationCode(),
            'state' => true, // ← AGREGAR ESTA LÍNEA
        ]);

        //Enviar correo
        EmailService::enviarCorreoReserva($customer->load('clienteType'), $reservation, $waitingMinutes);

        return response()->json([
            'state' => true,
            'message' => 'Reservación creada correctamente y correo enviado.',
            'reservation' => new PublicReservationResource($reservation->load('customer'))
        ]);
    }
    public function store(StoreReservationRequest $request)
    {
        Gate::authorize('create', Reservation::class);
        $validated = $request->validated();

        //Obtener configuración actual
        $config = ReservationSetting::latest()->first();
        $waitingMinutes = $config?->waiting_minutes ?? 2;

        $hour = Carbon::createFromFormat('H:i', $validated['hour']);
        $waitingHour = $hour->copy()->addMinutes($waitingMinutes)->format('H:i');

        $validated['waiting_hour'] = $waitingHour;

        $reservation = Reservation::create($validated);

        // Obtener el cliente (ya existente)
        $cliente = $reservation->customer()->with('clienteType')->first();

        // Enviar correo de confirmación
        EmailService::enviarCorreoReserva($cliente, $reservation, $waitingMinutes);
        return response()->json([
            'state' => true,
            'message' => 'Reservación registrada correctamente y correo enviado.',
            'reservation' => new ReservationResource($reservation->load('customer'))
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
        $cliente = $reservation->customer;

        //Obtener configuración actual
        $config = ReservationSetting::latest()->first();
        $waitingMinutes = $config?->waiting_minutes ?? 2;

        // Si el campo "hour" está presente en la actualización, recalculamos la hora de espera
        if (isset($validated['hour'])) {
            $hour = Carbon::createFromFormat('H:i', $validated['hour']);
            $waitingHour = $hour->copy()->addMinutes($waitingMinutes)->format('H:i');
            $validated['waiting_hour'] = $waitingHour;
        }

        // --- CASO 1: Inactivación manual (por el cliente) ---
        if (array_key_exists('state', $validated) && $validated['state'] === false) {
            if ($cliente && $cliente->state) {
                $cliente->update(['state' => false]);
            }

            // Solo enviar correo si es el cliente quien inactiva (no expiración automática)
            EmailService::enviarCorreoInactivacionReserva($cliente, $reservation);

            $reservation->update($validated);
            return response()->json([
                'state' => true,
                'message' => 'Reservación e información del cliente inactivadas correctamente. Correo enviado.',
                'reservation' => $reservation->refresh()
            ]);
        }

        // --- CASO 2: Actualización normal ---
        $reservation->update($validated);

        // Verificar si se modificaron number_people, date o hour
        if ($request->hasAny(['number_people', 'date', 'hour'])) {
            EmailService::enviarCorreoActualizacionReserva($cliente, $reservation, $waitingMinutes);
        }

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
    public function exportCsv()
    {
        return Excel::download(new ReservationExport, 'Reservaciones.csv', ExcelFormat::CSV);
    }
}