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
    <form action="{{ route('appointments.store', $barbershop) }}" method="POST">
      @csrf
      <input type="hidden" name="service_id" value="{{ $service->id }}">

      <div class="mb-6">
        <h2 class="text-xl font-black text-gray-900 mb-4">Horarios Disponibles</h2>
        @foreach($days as $day)
          <div class="mb-4">
            <h3 class="text-lg font-bold text-emerald-900 mb-2">{{ $day['date']->format('l, d M Y') }}</h3>
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
        @endforeach
      </div>

      <div class="flex justify-end">
        <button type="submit" class="eb-button px-6 py-3">
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
