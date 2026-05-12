@extends('layouts.email')

@section('title', 'Nueva solicitud de barberia')
@section('header_color', '#4F46E5')
@section('header_title', 'Nueva solicitud de barberia')

@section('content')
    <div class="content">
        <p>Hola,</p>

        <p>Se ha recibido una nueva solicitud para crear una barberia en EasyBooking.</p>

        <div class="details">
            <h3>Datos de la solicitud:</h3>
            <p><strong>Barberia:</strong> {{ $barbershopRequest->name }}</p>
            <p><strong>Direccion:</strong> {{ $barbershopRequest->address }}</p>
            <p><strong>Telefono:</strong> {{ $barbershopRequest->phone }}</p>
            <p><strong>Visibilidad inicial:</strong> Privada hasta aprobacion del administrador</p>
            <p><strong>Solicitante:</strong> {{ $requester->name }}</p>
            <p><strong>Email:</strong> {{ $requester->email }}</p>
        </div>

        <p>Revisa el panel de administracion para aceptar o rechazar esta solicitud.</p>

    </div>
@endsection
