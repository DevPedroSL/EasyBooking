@extends('layouts.email');

@section('title', 'Solicitud de barberia rechazada')
@section('header_color', '#EF4444')
@section('header_title', 'Solicitud rechazada')

@section('content')
    <p>Hola {{ $barbershop->barber->name }},</p>
    <p>Has recibido una nueva reserva de cita en tu barbería <strong>{{ $barbershop->name }}</strong>.</p>

    <div class="content">
        <p>Hola {{ $requester->name }},</p>

        <p>Tu solicitud para crear la barberia <strong>{{ $barbershopRequest->name }}</strong> ha sido rechazada.</p>

        <div class="details">
            <h3>Datos de la solicitud:</h3>
            <p><strong>Nombre:</strong> {{ $barbershopRequest->name }}</p>
            <p><strong>Direccion:</strong> {{ $barbershopRequest->address }}</p>
            <p><strong>Telefono:</strong> {{ $barbershopRequest->phone }}</p>
            @if($barbershopRequest->rejection_reason)
                <p><strong>Motivo:</strong> {{ $barbershopRequest->rejection_reason }}</p>
            @endif
        </div>

        <p>Puedes revisar los datos y enviar una nueva solicitud si lo necesitas.</p>
    </div>

@endsection