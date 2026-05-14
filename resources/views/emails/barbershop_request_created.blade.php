@extends('layouts.email')

@section('title', 'Nueva solicitud de barbería')
@section('header_color', '#4F46E5')
@section('header_title', 'Nueva solicitud de barbería')

@section('content')
    <p>Hola,</p>

    <p>Se ha recibido una nueva solicitud para crear una barbería en EasyBooking.</p>

    <div class="details">
        <h3>Datos de la solicitud</h3>
        <p><strong>Barbería:</strong> {{ $barbershopRequest->name }}</p>
        <p><strong>Dirección:</strong> {{ $barbershopRequest->address }}</p>
        <p><strong>Teléfono:</strong> {{ $barbershopRequest->phone }}</p>
        <p><strong>Visibilidad inicial:</strong> Privada hasta aprobación del administrador</p>
        <p><strong>Solicitante:</strong> {{ $requester->name }}</p>
        <p><strong>Email:</strong> {{ $requester->email }}</p>
    </div>

    <div class="notice">
        Revisa el panel de administración para aceptar o rechazar esta solicitud.
    </div>
@endsection
