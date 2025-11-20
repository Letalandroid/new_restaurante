<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public static function enviarMensaje(string $telefono, string $mensaje): bool
    {
        $instance = env('ULTRAMSG_INSTANCE_ID');
        $token = env('ULTRAMSG_TOKEN');
        $apiUrl = rtrim(env('ULTRAMSG_API_URL'), '/'); // 🔥 elimina barra final si existe

        // Asegurar formato internacional (+51 para Perú)
        if (!str_starts_with($telefono, '+')) {
            $telefono = '+51' . $telefono;
        }

        $endpoint = "$apiUrl/$instance/messages/chat";

        $response = Http::post($endpoint, [
            'token' => $token,
            'to' => $telefono,
            'body' => $mensaje,
            'priority' => 10,
        ]);

        return $response->successful();
    }
}
