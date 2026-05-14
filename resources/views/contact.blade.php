@extends('layouts.app')

@section('title', 'Contacto - EasyBooking')

@section('content')
@php($contactEmail = config('mail.from.address'))

<div class="page-shell max-w-3xl">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Contáctanos</h1>
            <p class="page-subtitle">Estamos aquí para ayudarte con tu experiencia en EasyBooking.</p>
        </div>
    </div>

    <div class="eb-panel p-8">
        <p class="text-base leading-7 text-slate-700">
            Si necesitas ayuda con una reserva, con tu cuenta o con la gestión de tu barbería, contáctanos aquí:
            <a href="mailto:{{ $contactEmail }}" class="font-bold text-violet-700 underline underline-offset-4">{{ $contactEmail }}</a>.
        </p>
        <p class="mt-4 text-base leading-7 text-slate-700">
            Cuéntanos qué necesitas y revisaremos tu mensaje lo antes posible. También puedes consultar el estado de tus citas desde <span class="font-bold text-slate-900">Mis Citas</span> si ya has iniciado sesión.
        </p>
        <div class="mt-6">
            <a href="{{ route('inicio') }}" class="eb-button px-6 py-3">Volver al inicio</a>
        </div>
    </div>
</div>
@endsection
