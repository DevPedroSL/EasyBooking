@extends('layouts.email')

@section('title', 'Cita aceptada')
@section('header_color', '#10B981')
@section('header_title', '¡Cita aceptada!')

@section('content')
    <p>Hola {{ $client->name }},</p>

    <p>¡Buenas noticias! Tu cita en <strong>{{ $barbershop->name }}</strong> ha sido aceptada.</p>

    <div class="appointment-details" style="margin:24px 0;padding:20px;border:1px solid #e5e7eb;border-radius:14px;background-color:#f9fafb;">
        <h3 style="margin:0 0 14px;color:#111827;font-size:18px;">Detalles de tu cita</h3>
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

    <div class="notice" style="margin:22px 0;padding:14px 16px;border-left:4px solid #10B981;border-radius:10px;background-color:#f8fafc;">
        Te esperamos en la barbería a la hora acordada.
    </div>

    <p>¡Gracias por usar EasyBooking!</p>
@endsection
