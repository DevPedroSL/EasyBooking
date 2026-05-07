@extends('layouts.app')

@section('title', 'Agenda - ' . $barbershop->name)

@section('content')
<div class="page-shell max-w-5xl">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Agenda</h1>
            <p class="page-subtitle">{{ $barbershop->name }} · {{ $selectedDate->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('appointments.barber') }}" class="eb-button px-5 py-3">Gestionar citas</a>
    </div>

    <div
        class="eb-panel booking-calendar-panel mb-6 overflow-hidden p-6"
        x-data="{ visibleMonth: @js($initialVisibleMonthIndex) }"
    >
        <div class="booking-calendar-head mb-6">
            <div>
                <p class="booking-calendar-kicker">Seleccionar dia</p>
                <h2 class="text-xl font-black text-gray-900">Mes actual y siguiente</h2>
            </div>
            <p class="booking-calendar-note">Elige un dia con horario para ver las horas ocupadas y libres.</p>
        </div>

        <div class="booking-months-stack">
            @foreach($calendarMonths as $month)
                <div class="booking-month-shell" x-cloak x-show="visibleMonth === {{ $loop->index }}">
                    <div class="booking-month-title">
                        {{ $month['label'] }}
                        <button
                            type="button"
                            class="booking-month-nav-button"
                            @click="visibleMonth = Math.max(0, visibleMonth - 1)"
                            :disabled="visibleMonth === 0"
                        >
                            <
                        </button>
                        <button
                            type="button"
                            class="booking-month-nav-button"
                            @click="visibleMonth = Math.min({{ count($calendarMonths) - 1 }}, visibleMonth + 1)"
                            :disabled="visibleMonth === {{ count($calendarMonths) - 1 }}"
                        >
                            >
                        </button>
                    </div>

                    <div class="booking-month-weekdays">
                        <span>Lun.</span>
                        <span>Mar.</span>
                        <span>Mie.</span>
                        <span>Jue.</span>
                        <span>Vie.</span>
                        <span>Sab.</span>
                        <span>Dom.</span>
                    </div>

                    <div class="booking-month-grid">
                        @foreach($month['days'] as $day)
                            @if($day === null)
                                <div class="booking-day-spacer" aria-hidden="true"></div>
                            @else
                                @php($isSelectableDay = $day['is_selectable'])

                                @if($isSelectableDay)
                                    <a
                                        href="{{ route('appointments.agenda', ['date' => $day['iso_date']]) }}#agenda-summary"
                                        class="booking-day-button {{ $day['is_selected'] ? 'is-selected' : '' }}"
                                        aria-label="Ver agenda del {{ $day['date']->format('d/m/Y') }}"
                                    >
                                        <span class="booking-day-week">{{ $day['weekday_label'] }}</span>
                                        <span class="booking-day-number">{{ $day['day_number'] }}</span>
                                        <span class="booking-day-pill">
                                            @if($day['active_appointment_count'] === 1)
                                                1 cita
                                            @elseif($day['active_appointment_count'] > 1)
                                                {{ $day['active_appointment_count'] }} citas
                                            @else
                                                Sin citas
                                            @endif
                                        </span>
                                    </a>
                                @else
                                    <button
                                        type="button"
                                        class="booking-day-button is-unavailable is-empty {{ $day['is_selected'] ? 'is-selected' : '' }} {{ $day['is_past'] ? 'is-past' : '' }}"
                                        aria-disabled="true"
                                        disabled
                                    >
                                        <span class="booking-day-week">{{ $day['weekday_label'] }}</span>
                                        <span class="booking-day-number">{{ $day['day_number'] }}</span>
                                        <span class="booking-day-pill">
                                            @if($day['is_past'])
                                                Pasado
                                            @else
                                                Cerrado
                                            @endif
                                        </span>
                                    </button>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[16rem_1fr]">
        <aside id="agenda-summary" class="eb-panel scroll-mt-24 p-6">
            <p class="text-sm font-bold uppercase text-gray-500">Resumen</p>
            <p class="mt-3 text-3xl font-black text-gray-900">{{ $appointments->count() }}</p>
            <p class="text-sm font-semibold text-gray-600">citas activas</p>

            <div class="mt-6 space-y-3 text-sm text-gray-700">
                @if($schedules->isNotEmpty())
                    <div>
                        <p class="font-bold text-gray-500">Horario</p>
                        <div class="mt-1 space-y-1">
                            @foreach($schedules as $scheduleSlot)
                                <p>{{ \Carbon\Carbon::parse($scheduleSlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($scheduleSlot->end_time)->format('H:i') }}</p>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="font-bold text-gray-500">Horas ocupadas</p>
                        <p class="mt-1">{{ collect($agendaSlots)->where('is_busy', true)->count() }} de {{ count($agendaSlots) }}</p>
                    </div>
                @else
                    <p class="rounded-lg bg-gray-100 p-3 font-semibold text-gray-700">La barbería no tiene horario para este día.</p>
                @endif
            </div>
        </aside>

        <section class="eb-panel overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-xl font-black text-gray-900">{{ $selectedDate->format('d/m/Y') }}</h2>
            </div>

            @if($schedules->isEmpty())
                <div class="p-8 text-gray-600">
                    No hay horario configurado para este día.
                </div>
            @elseif(count($agendaSlots) === 0)
                <div class="p-8 text-gray-600">
                    No hay franjas disponibles en el horario configurado.
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach($agendaSlots as $slot)
                        <div class="grid gap-4 px-6 py-5 md:grid-cols-[9rem_1fr] {{ $slot['is_busy'] ? 'bg-red-50/70' : 'bg-white' }}">
                            <div>
                                <p class="text-lg font-black text-gray-900">
                                    {{ $slot['start']->format('H:i') }} - {{ $slot['end']->format('H:i') }}
                                </p>
                                <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $slot['is_busy'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $slot['is_busy'] ? 'Ocupada' : 'Libre' }}
                                </span>
                            </div>

                            <div class="space-y-3">
                                @forelse($slot['appointments'] as $appointment)
                                    <div class="rounded-lg border border-red-100 bg-white p-4 shadow-sm">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="font-black text-gray-900">{{ $appointment->client->name }}</p>
                                                <p class="text-sm text-gray-600">{{ $appointment->service->name }} · {{ $appointment->service->duration }} min</p>
                                                <p class="text-sm text-gray-500">
                                                    {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}
                                                </p>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $appointment->status === 'accepted' ? 'bg-violet-100 text-violet-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $appointment->status === 'accepted' ? 'Aceptada' : 'Pendiente' }}
                                                </span>
                                                <a href="{{ route('appointments.show', $appointment) }}" class="text-sm font-bold text-violet-700 hover:text-violet-900">Ver detalles</a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm font-medium text-gray-500">No hay citas en esta hora.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.location.hash === '#agenda-summary') {
            document.getElementById('agenda-summary')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        }
    });
</script>
@endsection
