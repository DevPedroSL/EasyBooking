@extends('layouts.app')

@section('title', 'Mis Citas')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4">
  <div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Mis Citas</h1>
  </div>

  <div class="space-y-4">
    @forelse($appointments as $appointment)
      <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-xl font-semibold text-gray-800">{{ $appointment->barbershop->name }}</h3>
            <p class="text-gray-600">{{ $appointment->service->name }}</p>
            <p class="text-sm text-gray-500">{{ $appointment->start_time }} - {{ $appointment->end_time }}</p>
          </div>
          <div class="text-right">
            <span class="px-3 py-1 rounded-full text-sm font-medium
              @if($appointment->status == 'pending') bg-yellow-100 text-yellow-800
              @elseif($appointment->status == 'accepted') bg-green-100 text-green-800
              @elseif($appointment->status == 'completed') bg-blue-100 text-blue-800
              @else bg-red-100 text-red-800 @endif">
              {{ ucfirst($appointment->status) }}
            </span>
          </div>
        </div>
      </div>
    @empty
      <p class="text-gray-500 text-center py-10">No tienes citas reservadas.</p>
    @endforelse
  </div>
</div>
@endsection