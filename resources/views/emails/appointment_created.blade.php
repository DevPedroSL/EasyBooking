@extends('layouts.email')

@section('title', 'Nueva solicitud de cita')
@section('header_color', '#4F46E5')
@section('header_title', 'Nueva solicitud de cita')

@section('content')
    <p>Hola {{ $barbershop->barber->name }},</p>
    <p>Has recibido una nueva reserva de cita en tu barbería <strong>{{ $barbershop->name }}</strong>.</p>

    <div class="appointment-details" style="margin:24px 0;padding:20px;border:1px solid #e5e7eb;border-radius:14px;background-color:#f9fafb;">
        <h3 style="margin:0 0 14px;color:#111827;font-size:18px;">Detalles de la cita</h3>
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


    <div class="notice" style="margin:22px 0;padding:14px 16px;border-left:4px solid #4F46E5;border-radius:10px;background-color:#f8fafc;">
        Por favor, revisa tu panel de barbero para gestionar esta cita.
    </div>
@endsection
