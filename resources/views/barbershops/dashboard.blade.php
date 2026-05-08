@extends('layouts.app')

@section('title', 'Mi barberia')

@section('content')
<style>
    .barbershop-dashboard {
        display: grid;
        gap: 1.5rem;
    }

    .barbershop-dashboard-hero,
    .barbershop-dashboard-action,
    .barbershop-dashboard-stat {
        border: 1px solid var(--eb-line);
        border-radius: 8px;
        background: var(--eb-surface);
        box-shadow: 0 14px 28px rgba(24, 26, 56, 0.08);
    }

    .barbershop-dashboard-hero {
        display: grid;
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .barbershop-dashboard-image {
        min-height: 13rem;
        overflow: hidden;
        border-radius: 8px;
        background: #eee7fb;
    }

    .barbershop-dashboard-image img {
        width: 100%;
        height: 100%;
        min-height: 13rem;
        object-fit: cover;
    }

    .barbershop-dashboard-placeholder {
        display: grid;
        min-height: 13rem;
        place-items: center;
        padding: 1rem;
        color: var(--eb-forest);
        font-weight: 900;
        text-align: center;
    }

    .barbershop-dashboard-stats,
    .barbershop-dashboard-actions {
        display: grid;
        gap: 1rem;
    }

    .barbershop-dashboard-stat {
        padding: 1rem;
    }

    .barbershop-dashboard-action {
        display: flex;
        min-height: 9rem;
        flex-direction: column;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem;
        transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
    }

    .barbershop-dashboard-action:hover {
        border-color: rgba(103, 74, 157, 0.45);
        box-shadow: 0 18px 34px rgba(24, 26, 56, 0.12);
        transform: translateY(-2px);
    }

    .barbershop-dashboard-action-icon {
        display: inline-flex;
        width: 2.75rem;
        height: 2.75rem;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #f0e7ff;
        color: var(--eb-forest);
    }

    @media (min-width: 768px) {
        .barbershop-dashboard-hero {
            grid-template-columns: minmax(0, 1.3fr) minmax(18rem, 0.7fr);
            align-items: stretch;
        }

        .barbershop-dashboard-stats {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .barbershop-dashboard-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<div class="page-shell max-w-6xl">
    <div class="barbershop-dashboard">
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

        <section class="barbershop-dashboard-hero">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.2em] text-violet-700">Mi barberia</p>
                <h1 class="mt-3 text-4xl font-black text-gray-900">{{ $barbershop->name }}</h1>
                <p class="mt-3 text-base font-semibold text-gray-700">{{ $barbershop->address }}</p>
                <p class="mt-1 text-sm font-semibold text-gray-600">{{ $barbershop->phone }}</p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <span class="rounded-full bg-violet-100 px-4 py-2 text-sm font-black text-violet-800">
                        {{ $barbershop->visibility === 'public' ? 'Publica' : 'Privada' }}
                    </span>
                    <span class="rounded-full bg-white px-4 py-2 text-sm font-black text-gray-700 ring-1 ring-violet-100">
                        Intervalos de {{ $barbershop->slot_interval_minutes }} min
                    </span>
                </div>
            </div>

            <div class="barbershop-dashboard-image">
                @if($barbershop->image_url)
                    <img src="{{ $barbershop->image_url }}" alt="{{ $barbershop->name }}">
                @else
                    <div class="barbershop-dashboard-placeholder">Sin imagen principal</div>
                @endif
            </div>
        </section>

        <section class="barbershop-dashboard-stats" aria-label="Resumen">
            <div class="barbershop-dashboard-stat">
                <p class="text-sm font-bold text-gray-600">Servicios</p>
                <p class="mt-2 text-3xl font-black text-gray-900">{{ $barbershop->services_count }}</p>
            </div>
            <div class="barbershop-dashboard-stat">
                <p class="text-sm font-bold text-gray-600">Horarios</p>
                <p class="mt-2 text-3xl font-black text-gray-900">{{ $barbershop->schedules_count }}</p>
            </div>
            <div class="barbershop-dashboard-stat">
                <p class="text-sm font-bold text-gray-600">Citas</p>
                <p class="mt-2 text-3xl font-black text-gray-900">{{ $barbershop->appointments_count }}</p>
            </div>
            <div class="barbershop-dashboard-stat">
                <p class="text-sm font-bold text-gray-600">Pendientes</p>
                <p class="mt-2 text-3xl font-black text-gray-900">{{ $barbershop->pending_appointments_count }}</p>
            </div>
        </section>

        <section class="barbershop-dashboard-actions" aria-label="Acciones de barberia">
            <a href="{{ route('barbershops.editMy') }}" class="barbershop-dashboard-action">
                <span class="barbershop-dashboard-action-icon">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"></path></svg>
                </span>
                <span>
                    <span class="block text-xl font-black text-gray-900">Editar barberia</span>
                    <span class="mt-1 block text-sm font-semibold text-gray-600">Datos, fotos y visibilidad</span>
                </span>
            </a>

            <a href="{{ route('barbershops.schedule.edit') }}" class="barbershop-dashboard-action">
                <span class="barbershop-dashboard-action-icon">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"></path></svg>
                </span>
                <span>
                    <span class="block text-xl font-black text-gray-900">Horario</span>
                    <span class="mt-1 block text-sm font-semibold text-gray-600">Dias, tramos y frecuencia de citas</span>
                </span>
            </a>

            <a href="{{ route('barbershops.services.index') }}" class="barbershop-dashboard-action">
                <span class="barbershop-dashboard-action-icon">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h15M6 12h15M6 18h15M3 6h.01M3 12h.01M3 18h.01"></path></svg>
                </span>
                <span>
                    <span class="block text-xl font-black text-gray-900">Servicios</span>
                    <span class="mt-1 block text-sm font-semibold text-gray-600">Precios, duraciones e imagenes</span>
                </span>
            </a>

            <a href="{{ route('appointments.agenda') }}" class="barbershop-dashboard-action">
                <span class="barbershop-dashboard-action-icon">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16m-16 8V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"></path></svg>
                </span>
                <span>
                    <span class="block text-xl font-black text-gray-900">Agenda</span>
                    <span class="mt-1 block text-sm font-semibold text-gray-600">Calendario y ocupacion diaria</span>
                </span>
            </a>

            <a href="{{ route('appointments.barber') }}" class="barbershop-dashboard-action">
                <span class="barbershop-dashboard-action-icon">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg>
                </span>
                <span>
                    <span class="block text-xl font-black text-gray-900">Gestionar citas</span>
                    <span class="mt-1 block text-sm font-semibold text-gray-600">Pendientes, aceptadas y rechazadas</span>
                </span>
            </a>
        </section>
    </div>
</div>
@endsection
