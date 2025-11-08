<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    public static function enviarCorreoReserva($cliente, $reserva, $waitingMinutes)
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Port       = env('MAIL_PORT');

            // Codificación UTF-8
            $mail->CharSet = 'UTF-8';

            // Remitente
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));

            // Destinatario
            $mail->addAddress($cliente->email, $cliente->name . ' ' . $cliente->lastname);

            // Asunto
            $mail->Subject = 'Confirmación de Reservación - Restaurante Sabor Andino';

            // Tipo de cliente
            $tipoCliente = $cliente->clienteType->name ?? 'No especificado';

            // URL de WhatsApp (formato internacional: +51 para Perú)
            $whatsappLink = 'https://wa.me/51990800121?text=Hola%2C%20quisiera%20consultar%20sobre%20mi%20reservaci%C3%B3n.';

            // Contenido HTML con botón incluido
            $mail->isHTML(true);
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 30px;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>

                    <div style='background-color: #d97706; color: white; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0; font-size: 22px;'>🍽️ Confirmación de Reservación</h2>
                        <p style='margin: 5px 0 0 0; font-size: 15px;'>Restaurante Sabor Andino</p>
                    </div>

                    <div style='padding: 25px; color: #333; font-size: 15px; line-height: 1.6;'>
                        <h3 style='margin-top: 0; font-size: 18px;'>¡Gracias por tu reservación, {$cliente->name}!</h3>
                        <p style='font-size: 15px;'>Hemos registrado correctamente tu reservación en <b>Sabor Andino</b>.</p>

                        <div style='margin-top: 20px; border: 1px solid #eee; border-radius: 8px; padding: 18px; background-color: #fef8f2;'>
                            <h4 style='color: #d97706; margin-bottom: 10px; font-size: 17px;'>🧍 Datos del Cliente</h4>
                            <p style='margin: 6px 0; font-size: 15px;'><b>Nombre:</b> {$cliente->name} {$cliente->lastname}</p>
                            <p style='margin: 6px 0; font-size: 15px;'><b>Teléfono:</b> {$cliente->phone}</p>
                            <p style='margin: 6px 0; font-size: 15px;'><b>Email:</b> {$cliente->email}</p>
                            <p style='margin: 6px 0; font-size: 15px;'><b>Tipo de Cliente:</b> {$tipoCliente}</p>
                            <p style='margin: 6px 0; font-size: 15px;'><b>Código (DNI/RUC):</b> {$cliente->codigo}</p>
                        </div>

                        <div style='margin-top: 20px; border: 1px solid #eee; border-radius: 8px; padding: 18px; background-color: #f3f4f6;'>
                            <h4 style='color: #d97706; margin-bottom: 10px; font-size: 17px;'>📅 Datos de la Reservación</h4>
                            <p style='margin: 6px 0; font-size: 15px;'><b>N° de personas:</b> {$reserva->number_people}</p>
                            <p style='margin: 6px 0; font-size: 15px;'><b>Fecha:</b> {$reserva->date}</p>
                            <p style='margin: 6px 0; font-size: 15px;'><b>Hora:</b> {$reserva->hour} pm</p>

                            <!-- NUEVAS LÍNEAS -->
                            <p style='margin: 6px 0; font-size: 15px;'><b>Tiempo de espera:</b> {$waitingMinutes} minutos</p>
                            <p style='margin: 6px 0; font-size: 15px;'><b>Hora límite de espera:</b> {$reserva->waiting_hour} pm</p>

                            <p style='margin: 6px 0; font-size: 15px;'><b>Código de Reservación:</b> 
                            <span style='font-weight:bold; color:#15803d;'>{$reserva->reservation_code}</span></p>

                            <!-- TEXTO EXPLICATIVO -->
                            <p style='margin-top: 15px; font-size: 14px; color: #555;'>
                                ⏰ Usted tiene un tiempo de espera de <b>{$waitingMinutes} minutos</b>, hasta las <b>{$reserva->waiting_hour} pm</b>. 
                                Luego de ese tiempo, la reservación podría expirar automáticamente.
                            </p>
                        </div>

                        <div style='margin-top: 30px; text-align: center;'>
                            <a href='{$whatsappLink}' target='_blank' style='display: inline-block; background-color: #d97706; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold;'>
                                💬 Contactar al personal
                            </a>
                            <p style='margin-top: 15px; font-size: 15px;'>Te esperamos con los mejores sabores. 🍷</p>
                            <p style='color: #666; font-size: 14px;'>— <i>Equipo de Restaurante Sabor Andino</i></p>
                        </div>
                    </div>

                    <div style='background-color: #f3f4f6; text-align: center; padding: 12px; font-size: 13px; color: #777;'>
                        © " . date('Y') . " Restaurante Sabor Andino. Todos los derechos reservados.
                    </div>
                </div>
            </div>
            ";

            // Enviar correo
            $mail->send();

            return true;
        } catch (Exception $e) {
            \Log::error('Error al enviar correo de reservación: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
