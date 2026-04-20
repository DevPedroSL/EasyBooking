@extends('layouts.app')

@section('title', "{{ $barbershop->name }} - EasyBooking")

@section('content')


<div class="max-w-4xl mx-auto py-10 px-4">
  <div class="flex flex-col md:flex-row items-center gap-6 mb-12">
    <img src="{{ asset('storage/logos/javi.png') }}" alt="Local" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
    <div>
      <h1 class="text-4xl font-extrabold text-gray-900">{{ $barbershop->name }}</h1>
      <p class="text-gray-600">{{ $barbershop->Description }}</p>
      <p class="text-gray-500 text-sm">{{ $barbershop->address }}</p>
    </div>
  </div>

  <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
    <div class="p-8">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Nuestros Servicios</h2>
      
      <div class="space-y-4">
        @foreach($barbershop->services as $service)
          <div class="flex items-center justify-between p-4 rounded-xl hover:bg-indigo-50 transition-colors border border-transparent hover:border-indigo-100 group">
            <div class="flex-1">
              <h4 class="text-lg font-bold text-gray-800">{{ $service->name }}</h4>
              <p class="text-gray-500 text-sm">{{ $service->description }}</p>
              <span class="text-indigo-600 text-sm font-medium">{{ $service->duration }} min</span>
            </div>
            <div class="text-right ml-4">
              <span class="block text-xl font-bold text-gray-900 mb-2">{{ $service->price }}€</span>
              <a href="{{ route('appointments.create', ['barbershop' => $barbershop, 'service' => $service]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold opacity-0 group-hover:opacity-100 transition-opacity shadow-lg shadow-indigo-200">
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
