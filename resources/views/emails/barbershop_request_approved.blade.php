@extends('layouts.email')

@section('title', 'Solicitud de barberia aceptada')
@section('header_color', '#10B981')
@section('header_title', 'Solicitud aceptada')

@section('content')
    <div class="content">
        <p>Hola {{ $requester->name }},</p>

        <p>Tu solicitud para crear la barberia <strong>{{ $barbershop?->name ?? $barbershopRequest->name }}</strong> ha sido aceptada.</p>

        <div class="details">
            <h3>Datos de tu barberia:</h3>
            <p><strong>Nombre:</strong> {{ $barbershop?->name ?? $barbershopRequest->name }}</p>
            <p><strong>Direccion:</strong> {{ $barbershop?->address ?? $barbershopRequest->address }}</p>
            <p><strong>Telefono:</strong> {{ $barbershop?->phone ?? $barbershopRequest->phone }}</p>
            <p><strong>Visibilidad:</strong> {{ ($barbershop?->visibility ?? $barbershopRequest->visibility) === 'public' ? 'Publica' : 'Privada' }}</p>
        </div>

        <p>Ya puedes entrar en tu panel de barberia para completar fotos, horarios y servicios.</p>
    </div>
@endsection
