<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita aceptada</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #10B981; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .appointment-details { background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Cita aceptada!</h1>
        </div>

        <div class="content">
            <p>Hola {{ $client->name }},</p>

            <p>¡Buenas noticias! Tu cita en <strong>{{ $barbershop->name }}</strong> ha sido aceptada.</p>

            <div class="appointment-details">
                <h3>Detalles de tu cita:</h3>
                <p><strong>Barbería:</strong> {{ $barbershop->name }}</p>
                <p><strong>Dirección:</strong> {{ $barbershop->address }}</p>
                <p><strong>Servicio:</strong> {{ $service->name }}</p>
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</p>
                <p><strong>Hora:</strong> {{ $appointment->start_time->format('H:i') }} - {{ $appointment->end_time->format('H:i') }}</p>
                @if($appointment->client_comment)
                <p><strong>Tu comentario:</strong> {{ $appointment->client_comment }}</p>
                @endif
                @if($appointment->barber_comment)
                <p><strong>Comentario del barbero:</strong> {{ $appointment->barber_comment }}</p>
                @endif
            </div>

            <p>Te esperamos en la barbería a la hora acordada.</p>

            <p>¡Gracias por usar EasyBooking!</p>

            <p>Saludos,<br>
            El equipo de EasyBooking</p>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este email.</p>
        </div>
    </div>
</body>
</html>