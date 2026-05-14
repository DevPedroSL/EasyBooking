@extends('layouts.email')

@section('title', 'Solicitud de barbería rechazada')
@section('header_color', '#EF4444')
@section('header_title', 'Solicitud rechazada')

@section('content')
    <p>Hola {{ $requester->name }},</p>

    <p>Tu solicitud para crear la barbería <strong>{{ $barbershopRequest->name }}</strong> ha sido rechazada.</p>

    <div class="details">
        <h3>Datos de la solicitud</h3>
        <p><strong>Nombre:</strong> {{ $barbershopRequest->name }}</p>
        <p><strong>Dirección:</strong> {{ $barbershopRequest->address }}</p>
        <p><strong>Teléfono:</strong> {{ $barbershopRequest->phone }}</p>
        @if($barbershopRequest->rejection_reason)
            <p><strong>Motivo:</strong> {{ $barbershopRequest->rejection_reason }}</p>
        @endif
    </div>

    <div class="notice">
        Puedes revisar los datos y enviar una nueva solicitud si lo necesitas.
    </div>
@endsection
