<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarNotificacionesReservasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $ahora = Carbon::now();
        $limite = $ahora->copy()->addMinutes(10);
        
        Log::info("🔔 Iniciando envío de notificaciones - Hora actual: {$ahora->format('H:i')}");

        // Obtener la fecha actual en formato Y-m-d (como está en la BD)
        $fechaHoy = $ahora->format('Y-m-d');
        
        $reservas = Reservation::with('customer')
            ->where('date', $fechaHoy)
            ->where('state', true)
            ->where('notification_sent', false)
            ->get()
            ->filter(function ($r) use ($ahora, $limite) {
                try {
                    // Combinar la fecha de la reserva con la hora
                    $fechaReserva = Carbon::parse($r->date);
                    $horaReserva = Carbon::parse($r->hour);
                    
                    // Crear objeto Carbon completo con fecha y hora
                    $fechaHoraReserva = $fechaReserva->setTime(
                        $horaReserva->hour, 
                        $horaReserva->minute
                    );
                    
                    // Verificar si está entre ahora y 10 minutos después
                    return $fechaHoraReserva->between($ahora, $limite);
                    
                } catch (\Exception $e) {
                    Log::error("Error procesando reserva {$r->id}: " . $e->getMessage());
                    return false;
                }
            });

        Log::info("📊 Reservas encontradas hoy: " . Reservation::where('date', $fechaHoy)->where('state', true)->count());
        Log::info("📊 Reservas a notificar: " . $reservas->count());

        foreach ($reservas as $reserva) {
            $cliente = $reserva->customer;
            if ($cliente && $cliente->phone) {
                $mensaje = "📅 *Recordatorio de reservación* \n\nEstimado/a {$cliente->name}, 
le recordamos que tiene una reservación hoy a las *{$reserva->hour}* pm 
para *{$reserva->number_people}* persona(s). ¡Lo esperamos! 🍽️";

                Log::info("📱 Intentando enviar a: {$cliente->phone}");
                
                $enviado = WhatsAppService::enviarMensaje($cliente->phone, $mensaje);
                
                if ($enviado) {
                    // Marcar como enviado para evitar duplicados
                    $reserva->update(['notification_sent' => true]);
                    Log::info("✅ Notificación enviada a {$cliente->phone} para reserva {$reserva->reservation_code}");
                } else {
                    Log::error("❌ Error enviando notificación a {$cliente->phone}");
                }
            } else {
                Log::warning("⚠️ Cliente sin teléfono para reserva {$reserva->id}");
            }
        }

        Log::info("🏁 Finalizado envío de notificaciones");
    }
}