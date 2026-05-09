@extends('layouts.email')

@section('title', 'Cita rechazada')
@section('header_color', '#EF4444')
@section('header_title', 'Cita rechazada')

@section('content')
    <p>Hola {{ $barbershop->barber->name }},</p>
    <p>Has recibido una nueva reserva de cita en tu barbería <strong>{{ $barbershop->name }}</strong>.</p>

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
    </div>
@endsection