@extends('layouts.app')

@section('title', 'Mis Citas')

@section('content')
<div class="page-shell max-w-4xl">
  <div class="page-heading">
    <div>
      <h1 class="page-title">Mis Citas</h1>
    </div>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg border border-violet-200 bg-violet-50 px-4 py-3 text-sm font-medium text-violet-800">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
      {{ session('error') }}
    </div>
  @endif

  <div class="space-y-4">
    @forelse($appointments as $appointment)
      <div class="eb-card">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-xl font-semibold text-gray-800">{{ $appointment->barbershop->name }}</h3>
            <p class="text-gray-600">{{ $appointment->service->name }}</p>
            <p class="text-sm text-gray-500">
              {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }} &bull;
              {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} &bull; {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}
            </p>
            @if($appointment->client_comment)
              <p class="mt-2 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($appointment->client_comment, 15) }}</p>
            @endif
            @php($barberComment = $appointment->barber_comment ?: $appointment->rejection_reason)
            @if(in_array($appointment->status, ['accepted', 'rejected'], true) && $barberComment)
              <p class="mt-2 text-sm font-medium {{ $appointment->status === 'rejected' ? 'text-red-700' : 'text-violet-700' }}">Comentario de la barbería: {{ \Illuminate\Support\Str::limit($barberComment, 80) }}</p>
            @endif
          </div>
          <div class="flex flex-col items-end gap-3 text-right">
            <span class="px-3 py-1 rounded-full text-sm font-medium
              @if($appointment->status == 'pending') bg-yellow-100 text-yellow-800
              @elseif($appointment->status == 'accepted') bg-violet-100 text-violet-800
              @elseif($appointment->status == 'completed') bg-blue-100 text-blue-800
              @else bg-red-100 text-red-800 @endif">
              {{ ucfirst($appointment->status) }}
            </span>
            <div class="flex flex-wrap items-center justify-end gap-3">
              <a href="{{ route('appointments.show', $appointment) }}" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-violet-200 px-4 py-2 text-sm font-bold text-violet-700 transition hover:bg-violet-50 hover:text-violet-900">Ver detalles</a>
              @if($appointment->status === 'pending')
                <form
                  method="POST"
                  action="{{ route('appointments.cancel', $appointment) }}"
                  data-confirm-title="Cancelar cita"
                  data-confirm-message="Vas a cancelar esta cita &bull; Si quieres reservarla de nuevo, tendras que hacerlo otra vez &bull;"
                  data-confirm-button="Cancelar cita"
                >
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="inline-flex min-h-9 items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-red-700">
                    Cancelar cita
                  </button>
                </form>
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <p class="text-gray-500 text-center py-10">No tienes citas reservadas &bull;</p>
    @endforelse
  </div>
</div>
@endsection
