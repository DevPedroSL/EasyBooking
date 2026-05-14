@extends('layouts.email')

@section('title', 'Nueva solicitud de barbería')
@section('header_color', '#4F46E5')
@section('header_title', 'Nueva solicitud de barbería')

@section('content')
    <p>Hola,</p>

    <p>Se ha recibido una nueva solicitud para crear una barbería en EasyBooking.</p>

    <div class="details" style="margin:24px 0;padding:20px;border:1px solid #e5e7eb;border-radius:14px;background-color:#f9fafb;">
        <h3 style="margin:0 0 14px;color:#111827;font-size:18px;">Datos de la solicitud</h3>
        <p><strong>Barbería:</strong> {{ $barbershopRequest->name }}</p>
        <p><strong>Dirección:</strong> {{ $barbershopRequest->address }}</p>
        <p><strong>Teléfono:</strong> {{ $barbershopRequest->phone }}</p>
        <p><strong>Visibilidad inicial:</strong> Privada hasta aprobación del administrador</p>
        <p><strong>Solicitante:</strong> {{ $requester->name }}</p>
        <p><strong>Email:</strong> {{ $requester->email }}</p>
    </div>

    <div class="notice" style="margin:22px 0;padding:14px 16px;border-left:4px solid #4F46E5;border-radius:10px;background-color:#f8fafc;">
        Revisa el panel de administración para aceptar o rechazar esta solicitud.
    </div>
@endsection
