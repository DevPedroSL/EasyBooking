@extends('layouts.app')

@section('title', $barbershop->name . ' - EasyBooking')

@section('content')


<div class="page-shell max-w-4xl">
  <div class="shop-hero mb-8">
    <div>
      <h1 class="text-4xl font-black">{{ $barbershop->name }}</h1>
      <p class="mt-3 max-w-2xl text-violet-50">{{ \Illuminate\Support\Str::limit($barbershop->Description, 50) }}</p>
      <p class="mt-2 text-sm font-semibold text-violet-200">{{ $barbershop->address }}</p>
    </div>
  </div>

  <div class="eb-panel overflow-hidden">
    <div class="p-8">
      <h2 class="text-2xl font-black text-gray-900 mb-6">Nuestros Servicios</h2>
      
      <div class="space-y-4">
        @foreach($barbershop->services as $service)
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
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
