@extends('layouts.app')

@section('title', 'Mis Citas')

@section('content')
<div class="page-shell max-w-4xl">
  <div class="page-heading">
    <div>
      <h1 class="page-title">Mis Citas</h1>
      <p class="page-subtitle">Tus reservas activas y su estado.</p>
    </div>
  </div>

  <div class="space-y-4">
    @forelse($appointments as $appointment)
      <div class="eb-card">
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
