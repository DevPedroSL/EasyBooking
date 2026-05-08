@extends('layouts.app')

@section('title', 'Modificar horario')

@section('content')
<style>
    .barbershop-schedule-page {
        display: grid;
        gap: 1.5rem;
    }

    .barbershop-schedule-hero,
    .barbershop-schedule-card {
        border: 1px solid var(--eb-line);
        border-radius: 8px;
        background: var(--eb-surface);
        box-shadow: 0 14px 28px rgba(24, 26, 56, 0.08);
    }

    .barbershop-schedule-hero {
        padding: 1.5rem;
    }

    .barbershop-schedule-card {
        padding: 1.5rem;
    }

    .barbershop-schedule-grid {
        display: grid;
        gap: 0.85rem;
        margin-top: 1.5rem;
    }

    .barbershop-schedule-day {
        border: 1px solid rgba(143, 106, 216, 0.18);
        border-radius: 8px;
        background: #fbf9ff;
        padding: 1rem;
    }

    .barbershop-schedule-times {
        display: grid;
        gap: 0.75rem;
        margin-top: 0.9rem;
    }

    @media (min-width: 768px) {
        .barbershop-schedule-times {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<div class="page-shell max-w-6xl">
    @php
        $schedulesByDay = $barbershop->schedules->sortBy('start_time')->groupBy('day_of_week');
        $storedScheduleDays = $schedulesByDay->keys()->map(fn ($day) => (int) $day)->all();
        $selectedScheduleDays = collect(old('schedule_days', $storedScheduleDays))->map(fn ($day) => (int) $day)->all();
    @endphp

    <div class="barbershop-schedule-page">
        <section class="barbershop-schedule-hero">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-violet-700">Mi barberia</p>
            <h1 class="mt-3 text-4xl font-black text-gray-900">Modificar horario</h1>
            <p class="mt-3 max-w-3xl text-base leading-7 text-gray-700">
                Ajusta los dias abiertos, los tramos de apertura y la frecuencia con la que aparecen huecos disponibles para reservar.
            </p>
        </section>

        @if(session('success'))
            <div class="rounded-lg border border-green-300 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-lg border border-red-300 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-300 bg-red-50 px-5 py-4 text-red-700">
                <p class="text-sm font-black uppercase tracking-[0.16em]">Revisa estos campos</p>
                <ul class="mt-3 list-disc ps-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('barbershops.schedule.update') }}" method="POST" class="barbershop-schedule-card">
            @csrf
            @method('PATCH')

            <div>
                <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">Horario</p>
                <h2 class="mt-2 text-2xl font-black text-gray-900">Cuando puede reservar el cliente</h2>
                <p class="mt-2 text-sm leading-6 text-gray-700">
                    Marca los dias abiertos y ajusta la apertura y cierre. Las reservas y la agenda usan este horario.
                </p>
                @error('schedule_days') <p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror

                <div class="mt-6">
                    <label for="slot_interval_minutes" class="mb-2 block text-sm font-bold text-gray-900">Frecuencia de citas</label>
                    <select name="slot_interval_minutes" id="slot_interval_minutes" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200">
                        <option value="15" @selected((int) old('slot_interval_minutes', $barbershop->slot_interval_minutes ?? 60) === 15)>Cada 15 minutos</option>
                        <option value="30" @selected((int) old('slot_interval_minutes', $barbershop->slot_interval_minutes ?? 60) === 30)>Cada 30 minutos</option>
                        <option value="45" @selected((int) old('slot_interval_minutes', $barbershop->slot_interval_minutes ?? 60) === 45)>Cada 45 minutos</option>
                        <option value="60" @selected((int) old('slot_interval_minutes', $barbershop->slot_interval_minutes ?? 60) === 60)>Cada 1 hora</option>
                    </select>
                    <p class="mt-2 text-xs font-medium text-gray-500">Define cada cuanto aparece un hueco disponible para reservar.</p>
                    @error('slot_interval_minutes') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="barbershop-schedule-grid">
                    @foreach($weekdays as $day => $label)
                        @php
                            $daySchedules = $schedulesByDay->get($day, collect())->values();
                        @endphp

                        <div class="barbershop-schedule-day">
                            <label class="inline-flex items-center gap-3 text-sm font-black text-gray-900">
                                <input
                                    type="checkbox"
                                    name="schedule_days[]"
                                    value="{{ $day }}"
                                    class="h-4 w-4 rounded border-gray-300 text-violet-700 focus:ring-violet-500"
                                    @checked(in_array($day, $selectedScheduleDays, true))
                                >
                                <span>{{ $label }}</span>
                            </label>

                            <div class="mt-4 space-y-4">
                                @for($interval = 0; $interval < 2; $interval++)
                                    @php
                                        $schedule = $daySchedules->get($interval);
                                        $defaultStartTime = $schedule
                                            ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i')
                                            : ($interval === 0 ? '10:00' : '');
                                        $defaultEndTime = $schedule
                                            ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i')
                                            : ($interval === 0 ? '20:00' : '');
                                    @endphp

                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.12em] text-violet-700">
                                            Tramo {{ $interval + 1 }}{{ $interval === 1 ? ' opcional' : '' }}
                                        </p>

                                        <div class="barbershop-schedule-times">
                                            <div>
                                                <label for="schedule_{{ $day }}_{{ $interval }}_start" class="mb-2 block text-sm font-bold text-gray-900">Apertura</label>
                                                <input
                                                    type="time"
                                                    name="schedules[{{ $day }}][{{ $interval }}][start_time]"
                                                    id="schedule_{{ $day }}_{{ $interval }}_start"
                                                    value="{{ old("schedules.$day.$interval.start_time", $defaultStartTime) }}"
                                                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                                                >
                                                @error("schedules.$day.$interval.start_time") <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                            <div>
                                                <label for="schedule_{{ $day }}_{{ $interval }}_end" class="mb-2 block text-sm font-bold text-gray-900">Cierre</label>
                                                <input
                                                    type="time"
                                                    name="schedules[{{ $day }}][{{ $interval }}][end_time]"
                                                    id="schedule_{{ $day }}_{{ $interval }}_end"
                                                    value="{{ old("schedules.$day.$interval.end_time", $defaultEndTime) }}"
                                                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                                                >
                                                @error("schedules.$day.$interval.end_time") <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('barbershops.dashboard') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-gray-300 px-6 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-100">
                    Volver a mi barberia
                </a>
                <button type="submit" class="eb-button px-8 py-3">
                    Guardar horario
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
