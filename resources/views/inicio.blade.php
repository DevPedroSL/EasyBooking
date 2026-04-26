@extends('layouts.app')

@section('title', 'Inicio - EasyBooking')

@section('content')
<div class="page-shell">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Barberías Disponibles</h1>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($barbershops as $barbershop)
        <div class="eb-card transition">
            <div class="mb-5">
                <h2 class="text-xl font-black text-gray-900">{{ $barbershop->name }}</h2>
            </div>
            <p class="text-gray-600 mb-5 min-h-16">{{ \Illuminate\Support\Str::limit($barbershop->Description ?? 'Servicio de barbería profesional', 50) }}</p>
            <p class="text-sm font-semibold text-gray-700 mb-5">{{ $barbershop->address }}</p>
            <a href="{{ route('barbershop', $barbershop->name) }}" 
               class="eb-button">
                Ver Servicios
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
