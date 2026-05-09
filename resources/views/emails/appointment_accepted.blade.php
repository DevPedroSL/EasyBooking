@extends('layouts.email');

@section('title', 'Cita aceptada')
@section('header_color', '#10B981')
@section('header_title', '¡Cita aceptada!')

@section('content')
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
@endsection