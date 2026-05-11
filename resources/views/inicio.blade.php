@extends('layouts.app')

@section('title', 'Inicio - EasyBooking')

@section('content')
<div class="page-shell">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Barberías Disponibles</h1>
        </div>
        @auth
            @if(!auth()->user()->barbershop && auth()->user()->role !== 'admin')
                <a href="{{ route('barbershop-requests.create') }}" class="eb-button px-5 py-3">Crear barbería</a>
            @endif
        @endauth
    </div>

    <div class="eb-panel mb-8 p-6">
        <form action="{{ route('inicio') }}" method="GET" class="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
            <div>
                <label for="search-name" class="mb-2 block text-sm font-bold text-gray-900">Buscar por nombre</label>
                <input
                    id="search-name"
                    name="name"
                    type="text"
                    value="{{ $name ?? '' }}"
                    placeholder="Ej: Barbería Central"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                >
            </div>

            <div>
                <label for="search-address" class="mb-2 block text-sm font-bold text-gray-900">Buscar por dirección</label>
                <input
                    id="search-address"
                    name="address"
                    type="text"
                    value="{{ $address ?? '' }}"
                    placeholder="Ej: Calle Mayor"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                >
            </div>

            <div class="flex gap-3 md:justify-end">
                <button type="submit" class="eb-button px-6 py-3">Buscar</button>
                @if(($name ?? '') !== '' || ($address ?? '') !== '')
                    <a href="{{ route('inicio') }}" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-gray-300 px-6 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-100">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div x-data="{ visibleCount: 9 }">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($barbershops as $barbershop)
                <div x-cloak x-show="visibleCount > {{ $loop->index }}" class="eb-card transition">
                    @if($barbershop->image_url)
                        <div class="mb-5 flex h-52 w-full items-center justify-center rounded-2xl bg-white p-4 shadow-sm">
                            <img src="{{ $barbershop->image_url }}" alt="{{ $barbershop->name }}" class="h-full w-full object-contain">
                        </div>
                    @else
                        <div class="mb-5 flex h-52 w-full items-center justify-center rounded-2xl bg-violet-100 text-center">
                            <span class="px-6 text-lg font-black text-violet-800">{{ $barbershop->name }}</span>
                        </div>
                    @endif
                    <div class="mb-5">
                        <h2 class="text-xl font-black text-gray-900">{{ $barbershop->name }}</h2>
                    </div>
                    <div class="mb-5 space-y-1 text-sm font-semibold text-gray-700">
                        <p>{{ $barbershop->address }}</p>
                        @php($phoneHref = preg_replace('/\D+/', '', $barbershop->phone))
                        <a href="tel:{{ $phoneHref }}" class="inline-flex text-violet-700 transition hover:text-violet-900">
                            {{ $barbershop->phone }}
                        </a>
                    </div>
                    <a href="{{ route('barbershop', $barbershop->name) }}" class="eb-button">
                        Ver Servicios
                    </a>
                </div>
            @empty
                <div class="eb-panel p-8 text-center text-gray-600 md:col-span-2 lg:col-span-3">
                    No se han encontrado barberías con esos filtros.
                </div>
            @endforelse
        </div>

        @if($barbershops->count() > 9)
            <div class="mt-8 flex justify-center">
                <button
                    type="button"
                    x-show="visibleCount < {{ $barbershops->count() }}"
                    @click="visibleCount += 9"
                    class="eb-button px-6 py-3"
                >
                    Mostrar más
                </button>
            </div>
        @endif
    </div>
</div>
@endsection
