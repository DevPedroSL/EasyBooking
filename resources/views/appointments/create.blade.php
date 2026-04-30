@extends('layouts.app')

@section('title', 'Reservar Cita - ' . $barbershop->name)

@section('content')
<div class="page-shell max-w-4xl">
  <div class="page-heading">
    <div>
      <h1 class="page-title">Reservar Cita</h1>
      <p class="page-subtitle">{{ $barbershop->name }} · {{ $service->name }}</p>
    </div>
  </div>

  <div class="eb-panel overflow-hidden p-8">
    <form action="{{ route('appointments.confirm', $barbershop) }}" method="GET">
      <input type="hidden" name="barbershop_id" value="{{ $barbershop->id }}">
      <input type="hidden" name="service_id" value="{{ $service->id }}">

      <div class="mb-6">
        <h2 class="text-xl font-black text-gray-900 mb-4">Horarios Disponibles</h2>
        @error('datetime') <p class="mb-4 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror

        @forelse($days as $day)
          <div class="mb-4">
            <h3 class="text-lg font-bold text-violet-900 mb-2">{{ $day['date']->format('l, d M Y') }}</h3>
            <div class="grid grid-cols-4 gap-2">
              @foreach($day['slots'] as $slot)
                <label class="flex items-center">
                  <input type="radio" name="datetime" value="{{ $day['date']->format('Y-m-d') }} {{ $slot }}" required class="hidden peer">
                  <div class="slot-choice transition-colors">
                    {{ $slot }}
                  </div>
                </label>
              @endforeach
            </div>
          </div>
        @empty
          <p class="text-gray-600">No hay horarios disponibles para este servicio.</p>
        @endforelse
      </div>

      @if(!empty($days))
        @auth
          <div class="flex justify-end">
            <button type="submit" class="eb-button px-6 py-3">
              Continuar
            </button>
          </div>
        @else
          <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm font-black uppercase tracking-wide text-amber-800">Aviso</p>
            <p class="mt-2 text-sm font-medium text-amber-900">
              Para reservar una cita, selecciona una hora y después inicia sesión o regístrate. Guardaremos tu selección para que puedas confirmarla justo después.
            </p>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
              <button type="submit" formaction="{{ route('login') }}" class="eb-button px-6 py-3 text-center">
                Iniciar sesión
              </button>
              <button type="submit" formaction="{{ route('register') }}" class="rounded-xl border border-amber-300 px-6 py-3 text-center text-sm font-bold text-amber-900 transition hover:bg-amber-100">
                Registrarse
              </button>
            </div>
          </div>
        @endauth
      @endif
    </form>
  </div>
</div>
@endsection
