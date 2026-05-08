<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita rechazada</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #EF4444; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .appointment-details { background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cita rechazada</h1>
        </div>

        <div class="content">
            <p>Hola {{ $client->name }},</p>

            <p>Lamentamos informarte que tu cita en <strong>{{ $barbershop->name }}</strong> ha sido rechazada.</p>

            <div class="appointment-details">
                <h3>Detalles de la cita solicitada:</h3>
                <p><strong>Barbería:</strong> {{ $barbershop->name }}</p>
                <p><strong>Servicio:</strong> {{ $service->name }}</p>
                <p><strong>Fecha solicitada:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</p>
                <p><strong>Hora solicitada:</strong> {{ $appointment->start_time->format('H:i') }} - {{ $appointment->end_time->format('H:i') }}</p>
                @if($appointment->client_comment)
                <p><strong>Tu comentario:</strong> {{ $appointment->client_comment }}</p>
                @endif
                @if($appointment->rejection_reason)
                <p><strong>Motivo del rechazo:</strong> {{ $appointment->rejection_reason }}</p>
                @endif
            </div>

            <p>No te preocupes, puedes buscar otros horarios disponibles o contactar directamente con la barbería.</p>

            <p>Si tienes alguna pregunta, puedes contactar con el barbero al teléfono: {{ $barbershop->phone }}</p>

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