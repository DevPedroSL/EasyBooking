@extends('layouts.app')

@section('title', 'Servicios de Mi Barbería')

@section('content')
@php
    $servicesCount = $services->count();
@endphp

<div class="page-shell max-w-6xl service-management-page">
    <div class="eb-panel overflow-hidden service-overview">
        <div class="service-overview__hero">
            <div class="service-overview__copy">
                <p class="service-overview__eyebrow">Panel de servicios</p>
                <h1 class="service-overview__title">Servicios de {{ $barbershop->name }}</h1>
                <p class="service-overview__subtitle">Organiza aquí todos los servicios que ofreces. Puedes editar precios, duraciones o crear nuevos en un par de clics.</p>
            </div>

            <div class="service-overview__actions">
                <a href="{{ route('barbershops.editMy') }}" class="service-button service-button-muted">
                    Volver a datos generales
                </a>
                <a href="{{ route('barbershops.services.create') }}" class="service-button service-button-primary">
                    Crear nuevo servicio
                </a>
            </div>
        </div>

        <div class="service-overview__stats">
            <div class="service-stat">
                <span class="service-stat__label">Servicios activos</span>
                <strong class="service-stat__value">{{ $servicesCount }}</strong>
            </div>
        </div>

        @if(session('success'))
            <div class="service-feedback rounded border border-green-300 bg-green-100 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="service-feedback rounded border border-red-400 bg-red-100 p-4 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($services->isEmpty())
            <div class="service-empty-state">
                <h2 class="text-xl font-bold text-gray-900">Todavía no tienes servicios creados</h2>
                <p class="mt-2 text-sm text-gray-600">Empieza añadiendo tu primer servicio para que tus clientes puedan reservarlo.</p>
                <a href="{{ route('barbershops.services.create') }}" class="service-button service-button-primary mt-5">
                    Crear primer servicio
                </a>
            </div>
        @else
            <div class="service-list">
                @foreach ($services as $service)
                    <article class="service-card">
                        <div class="service-card__content">
                            <div class="service-card__header">
                                <div>
                                    <p class="service-card__kicker">Servicio {{ $loop->iteration }}</p>
                                    <h2 class="service-card__title">{{ $service->name }}</h2>
                                </div>
                                <div class="service-card__price">{{ number_format((float) $service->price, 2, ',', '.') }} €</div>
                            </div>

                            <p class="service-card__description">{{ $service->description ?: 'Sin descripción.' }}</p>

                            <div class="service-card__footer">
                                <div class="service-card__meta">
                                    <span class="service-pill service-pill-violet">{{ $service->duration }} min</span>
                                    <span class="service-pill {{ $service->visibility === 'public' ? 'service-pill-success' : 'service-pill-dark' }}">
                                        {{ $service->visibility === 'public' ? 'Disponible para reservar' : 'Oculto para los clientes' }}
                                    </span>
                                </div>

                                <div class="service-card__actions">
                                    <a href="{{ route('barbershops.services.edit', $service) }}" class="service-button service-button-primary service-button-small">
                                    Editar
                                </a>
                                    <form
                                        action="{{ route('barbershops.services.destroy', $service) }}"
                                        method="POST"
                                        data-confirm-title="Eliminar servicio"
                                        data-confirm-message="Vas a eliminar este servicio de la barberia. Esta accion no se puede deshacer."
                                        data-confirm-button="Eliminar servicio"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="service-button service-button-danger service-button-small">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
