@extends('layouts.app')

@section('title', 'Inicio - EasyBooking')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Barberías Disponibles</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($barbershops as $barbershop)
        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ $barbershop->name }}</h2>
            <p class="text-gray-600 mb-4">{{ $barbershop->description ?? 'Servicio de barbería profesional' }}</p>
            <a href="{{ route('barbershop', $barbershop->name) }}" 
               class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                Ver Servicios
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection