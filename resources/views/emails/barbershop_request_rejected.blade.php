@extends('layouts.email')

@section('title', 'Solicitud de barbería rechazada')
@section('header_color', '#EF4444')
@section('header_title', 'Solicitud rechazada')

@section('content')
    <p>Hola {{ $requester->name }},</p>

    <p>Tu solicitud para crear la barbería <strong>{{ $barbershopRequest->name }}</strong> ha sido rechazada.</p>

    <div class="details" style="margin:24px 0;padding:20px;border:1px solid #e5e7eb;border-radius:14px;background-color:#f9fafb;">
        <h3 style="margin:0 0 14px;color:#111827;font-size:18px;">Datos de la solicitud</h3>
        <p><strong>Nombre:</strong> {{ $barbershopRequest->name }}</p>
        <p><strong>Dirección:</strong> {{ $barbershopRequest->address }}</p>
        <p><strong>Teléfono:</strong> {{ $barbershopRequest->phone }}</p>
        @if($barbershopRequest->rejection_reason)
            <p><strong>Motivo:</strong> {{ $barbershopRequest->rejection_reason }}</p>
        @endif
    </div>

    <div class="notice" style="margin:22px 0;padding:14px 16px;border-left:4px solid #EF4444;border-radius:10px;background-color:#f8fafc;">
        Puedes revisar los datos y enviar una nueva solicitud si lo necesitas.
    </div>
@endsection
