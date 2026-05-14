@extends('layouts.email')

@section('title', 'Solicitud de barberia aceptada')
@section('header_color', '#10B981')
@section('header_title', 'Solicitud aceptada')

@section('content')
    <p>Hola {{ $requester->name }},</p>

    <p>Tu solicitud para crear la barbería <strong>{{ $barbershop?->name ?? $barbershopRequest->name }}</strong> ha sido aceptada.</p>

    <div class="details">
        <h3>Datos de tu barbería</h3>
        <p><strong>Nombre:</strong> {{ $barbershop?->name ?? $barbershopRequest->name }}</p>
        <p><strong>Dirección:</strong> {{ $barbershop?->address ?? $barbershopRequest->address }}</p>
        <p><strong>Teléfono:</strong> {{ $barbershop?->phone ?? $barbershopRequest->phone }}</p>
        <p><strong>Visibilidad:</strong> {{ ($barbershop?->visibility ?? $barbershopRequest->visibility) === 'public' ? 'Pública' : 'Privada' }}</p>
    </div>

    <div class="notice">
        Ya puedes entrar en tu panel de barbería para completar fotos, horarios y servicios. La barbería empieza como privada y puedes publicarla cuando esté lista.
    </div>
@endsection
