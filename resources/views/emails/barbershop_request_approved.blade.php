@extends('layouts.email');

@section('title', 'Solicitud de barberia aceptada')
@section('header_color', '#10B981')
@section('header_title', 'Solicitud aceptada')

@section('content')
    <p>Hola {{ $barbershop->barber->name }},</p>
    <p>Has recibido una nueva reserva de cita en tu barbería <strong>{{ $barbershop->name }}</strong>.</p>

    <div class="content">
        <p>Hola {{ $requester->name }},</p>

        <p>Tu solicitud para crear la barberia <strong>{{ $barbershopRequest->name }}</strong> ha sido aceptada.</p>

        <div class="details">
            <h3>Datos de tu barberia:</h3>
            <p><strong>Nombre:</strong> {{ $barbershopRequest->name }}</p>
            <p><strong>Direccion:</strong> {{ $barbershopRequest->address }}</p>
            <p><strong>Telefono:</strong> {{ $barbershopRequest->phone }}</p>
            <p><strong>Visibilidad:</strong> {{ $barbershopRequest->visibility === 'public' ? 'Publica' : 'Privada' }}</p>
        </div>

        <p>Ya puedes entrar en tu panel de barberia para completar fotos, horarios y servicios.</p>
    </div>
@endsection