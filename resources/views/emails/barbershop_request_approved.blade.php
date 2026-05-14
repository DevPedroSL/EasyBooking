@extends('layouts.email')

@section('title', 'Solicitud de barberia aceptada')
@section('header_color', '#10B981')
@section('header_title', 'Solicitud aceptada')

@section('content')
    <p>Hola {{ $requester->name }},</p>

    <p>Tu solicitud para crear la barbería <strong>{{ $barbershop?->name ?? $barbershopRequest->name }}</strong> ha sido aceptada.</p>

    <div class="details" style="margin:24px 0;padding:20px;border:1px solid #e5e7eb;border-radius:14px;background-color:#f9fafb;">
        <h3 style="margin:0 0 14px;color:#111827;font-size:18px;">Datos de tu barbería</h3>
        <p><strong>Nombre:</strong> {{ $barbershop?->name ?? $barbershopRequest->name }}</p>
        <p><strong>Dirección:</strong> {{ $barbershop?->address ?? $barbershopRequest->address }}</p>
        <p><strong>Teléfono:</strong> {{ $barbershop?->phone ?? $barbershopRequest->phone }}</p>
        <p><strong>Visibilidad:</strong> {{ ($barbershop?->visibility ?? $barbershopRequest->visibility) === 'public' ? 'Pública' : 'Privada' }}</p>
    </div>

    <div class="notice" style="margin:22px 0;padding:14px 16px;border-left:4px solid #10B981;border-radius:10px;background-color:#f8fafc;">
        Ya puedes entrar en tu panel de barbería para completar fotos, horarios y servicios. La barbería empieza como privada y puedes publicarla cuando esté lista.
    </div>
@endsection
