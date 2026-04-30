@extends('layouts.app')

@section('title', $barbershop->name . ' - EasyBooking')

@section('content')
@php
  $visibleServices = $barbershop->services->filter(fn ($service) => $service->isVisibleTo(auth()->user()));
@endphp


<div class="page-shell max-w-4xl">
  <div class="shop-hero mb-8">
    <div class="grid gap-6 md:grid-cols-[1.2fr_0.8fr] md:items-center">
      <div>
        <h1 class="text-4xl font-black">{{ $barbershop->name }}</h1>
        <p class="mt-3 max-w-2xl text-violet-50">{{ \Illuminate\Support\Str::limit($barbershop->Description, 50) }}</p>
        <p class="mt-2 text-sm font-semibold text-violet-200">{{ $barbershop->address }}</p>

        @if($barbershop->barber)
          <div class="mt-6 inline-flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-3 backdrop-blur-sm">
            <img src="{{ $barbershop->barber->avatar_url }}" alt="{{ $barbershop->barber->name }}" class="shop-avatar h-14 w-14 rounded-2xl object-cover">
            <div>
              <p class="text-lg font-bold">{{ $barbershop->barber->name }}</p>
            </div>
          </div>
        @endif
      </div>

      <div>
        @if($barbershop->image_url)
          <div class="shop-hero-media flex h-72 w-full items-center justify-center rounded-3xl" aria-label="{{ $barbershop->name }}">
            <img src="{{ $barbershop->image_url }}" alt="{{ $barbershop->name }}" class="h-full w-full object-contain">
          </div>
        @else
          <div class="flex h-72 w-full items-center justify-center rounded-3xl bg-white/10 p-6 text-center shadow-2xl">
            <span class="text-2xl font-black text-violet-50">{{ $barbershop->name }}</span>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="eb-panel overflow-hidden">
    <div class="p-8">
      <h2 class="text-2xl font-black text-gray-900 mb-6">Nuestros Servicios</h2>
      
      <div class="space-y-4">
        @forelse($visibleServices as $service)
          <div class="service-row flex items-center justify-between p-4 transition-colors group">
            <div class="flex-1">
              <h4 class="text-lg font-bold text-gray-800">{{ $service->name }}</h4>
              <p class="text-gray-500 text-sm">{{ \Illuminate\Support\Str::limit($service->description, 50) }}</p>
              <span class="text-violet-700 text-sm font-bold">{{ $service->duration }} min</span>
            </div>
            <div class="text-right ml-4">
              <span class="block text-xl font-bold text-gray-900 mb-2">{{ $service->price }}€</span>
              <a href="{{ route('appointments.create', ['barbershop' => $barbershop, 'service' => $service]) }}" class="eb-button eb-button-gold text-sm">
                Reservar
              </a>
            </div>
          </div>
        @empty
          <p class="text-gray-600">No hay servicios visibles disponibles en este momento.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
