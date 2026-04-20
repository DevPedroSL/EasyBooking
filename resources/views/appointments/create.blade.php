@extends('layouts.app')

@section('title', "Reservar Cita - {{ $barbershop->name }}")

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4">
  <div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Reservar Cita</h1>
    <p class="text-gray-600">Selecciona un horario disponible para {{ $service->name }}</p>
  </div>

  <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 p-8">
    <form action="{{ route('appointments.store', $barbershop) }}" method="POST">
      @csrf
      <input type="hidden" name="service_id" value="{{ $service->id }}">

      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Horarios Disponibles</h2>
        @foreach($days as $day)
          <div class="mb-4">
            <h3 class="text-lg font-medium text-gray-700 mb-2">{{ $day['date']->format('l, d M Y') }}</h3>
            <div class="grid grid-cols-4 gap-2">
              @foreach($day['slots'] as $slot)
                <label class="flex items-center">
                  <input type="radio" name="datetime" value="{{ $day['date']->format('Y-m-d') }} {{ $slot }}" required class="hidden peer">
                  <div class="peer-checked:bg-indigo-600 peer-checked:text-white bg-gray-100 text-gray-700 px-3 py-2 rounded-lg cursor-pointer hover:bg-gray-200 transition-colors">
                    {{ $slot }}
                  </div>
                </label>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>

      <div class="flex justify-end">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-colors">
          Reservar Cita
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // Ensure only one slot is selected per day
  document.querySelectorAll('input[name="date"]').forEach(dateInput => {
    dateInput.addEventListener('change', function() {
      // Uncheck other start_time if different date
      // But since date and start_time are separate, need to handle
    });
  });
</script>
@endsection