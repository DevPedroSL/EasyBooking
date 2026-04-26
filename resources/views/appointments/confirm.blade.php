@extends('layouts.app')

@section('title', 'Confirmar Cita - ' . $barbershop->name)

@section('content')
<div class="page-shell max-w-3xl">
  <div class="page-heading">
    <div>
      <h1 class="page-title">Confirmar Cita</h1>
      <p class="page-subtitle">{{ $barbershop->name }}</p>
    </div>
  </div>

  <div class="eb-panel overflow-hidden p-8">
    <div class="grid gap-4 sm:grid-cols-2 mb-8">
      <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
        <p class="text-sm font-bold text-gray-500 uppercase">Hora reservada</p>
        <p class="mt-2 text-2xl font-black text-gray-900">{{ $startTime->format('H:i') }}</p>
        <p class="text-sm text-gray-600">{{ $startTime->format('d/m/Y') }} · hasta las {{ $endTime->format('H:i') }}</p>
      </div>

      <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
        <p class="text-sm font-bold text-gray-500 uppercase">Servicio</p>
        <p class="mt-2 text-2xl font-black text-gray-900">{{ $service->name }}</p>
        <p class="text-sm text-gray-600">{{ $service->duration }} min</p>
      </div>
    </div>

    <form action="{{ route('appointments.store', $barbershop) }}" method="POST">
      @csrf
      <input type="hidden" name="service_id" value="{{ $service->id }}">
      <input type="hidden" name="datetime" value="{{ $datetime }}">

      <div class="mb-6">
        <label for="client_comment" class="block text-sm font-bold text-gray-700">Comentario para la barbería <span class="font-medium text-gray-500">(opcional)</span></label>
        <textarea
          name="client_comment"
          id="client_comment"
          rows="4"
          maxlength="150"
          class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500"
          placeholder="Escribe un comentario"
        >{{ old('client_comment') }}</textarea>
        <p class="mt-2 text-xs font-medium text-gray-500">Máximo 150 caracteres.</p>
        @error('client_comment') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
        @error('datetime') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="{{ route('appointments.create', ['barbershop' => $barbershop, 'service' => $service]) }}" class="text-sm font-bold text-violet-900 hover:text-violet-700">
          Cambiar horario
        </a>
        <button type="submit" class="eb-button px-6 py-3">
          Confirmar reserva
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
