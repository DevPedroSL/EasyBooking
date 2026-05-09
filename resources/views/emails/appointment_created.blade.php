@extends('layouts.email')

@section('title', 'Nueva solitiud de cita')
@section('header_color', '#4F46E5')
@section('header_title', 'Nueva solicitud de cita')

@section('content')
    <p>Hola {{ $barbershop->barber->name }},</p>
    <p>Has recibido una nueva reserva de cita en tu barbería <strong>{{ $barbershop->name }}</strong>.</p>

    <div class="appointment-details">
        <h3>Detalles de la cita:</h3>
        <p><strong>Cliente:</strong> {{ $client->name }}</p>
        <p><strong>Email:</strong> {{ $client->email }}</p>
        <p><strong>Teléfono:</strong> {{ $client->phone }}</p>
        <p><strong>Servicio:</strong> {{ $service->name }}</p>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</p>
        <p><strong>Hora:</strong> {{ $appointment->start_time->format('H:i') }} - {{ $appointment->end_time->format('H:i') }}</p>
        @if($appointment->client_comment)
            <p><strong>Comentario del cliente:</strong> {{ $appointment->client_comment }}</p>
        @endif
    </div>


    <p>Por favor, revisa tu panel de barbero para gestionar esta cita.</p>
@endsection