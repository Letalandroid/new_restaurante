<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public static function enviarMensaje(string $telefono, string $mensaje): bool
    {
        $instance = env('ULTRAMSG_INSTANCE_ID');
        $token = env('ULTRAMSG_TOKEN');
        $apiUrl = env('ULTRAMSG_API_URL');

        // Asegurar formato internacional (+51 para Perú)
        if (!str_starts_with($telefono, '+')) {
            $telefono = '+51' . $telefono;
        }

        $response = Http::post("$apiUrl/$instance/messages/chat", [
            'token' => $token,
            'to' => $telefono,
            'body' => $mensaje,
            'priority' => 10,
        ]);

        return $response->successful();
    }
}
