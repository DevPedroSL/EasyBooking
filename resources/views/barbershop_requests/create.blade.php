@extends('layouts.app')

@section('title', 'Crear barberia - EasyBooking')

@section('content')
<div class="page-shell max-w-4xl">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Crear barberia</h1>
            <p class="page-subtitle">Envia una solicitud para que el administrador revise y active tu barberia.</p>
        </div>
        <a href="{{ route('inicio') }}" class="eb-button px-5 py-3">Volver</a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($latestRequest?->status === 'pending')
        <section class="eb-panel p-8">
            <span class="inline-flex rounded-full bg-yellow-100 px-4 py-2 text-sm font-black text-yellow-800">Pendiente</span>
            <h2 class="mt-5 text-2xl font-black text-gray-900">{{ $latestRequest->name }}</h2>
            <div class="mt-4 space-y-2 text-sm font-semibold text-gray-700">
                <p>{{ $latestRequest->address }}</p>
                <p>{{ $latestRequest->phone }}</p>
                <p>Visibilidad: Privada hasta que el administrador la apruebe.</p>
            </div>
            <p class="mt-6 text-gray-600">Tu solicitud esta esperando revision del administrador.</p>
        </section>
    @else
        @if($latestRequest?->status === 'rejected')
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                Tu ultima solicitud fue rechazada.
                @if($latestRequest->rejection_reason)
                    <span class="block mt-2 font-medium">{{ $latestRequest->rejection_reason }}</span>
                @endif
            </div>
        @endif

        <form action="{{ route('barbershop-requests.store') }}" method="POST" class="eb-panel space-y-6 p-8">
            @csrf

            <div>
                <label for="name" class="mb-2 block text-sm font-bold text-gray-900">Nombre de la barberia</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                @error('name') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="address" class="mb-2 block text-sm font-bold text-gray-900">Direccion</label>
                <input id="address" name="address" type="text" value="{{ old('address') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                @error('address') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="mb-2 block text-sm font-bold text-gray-900">Telefono</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                @error('phone') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
            </div>

            <p class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-700">
                La barberia se creara como privada. Cuando el administrador apruebe la solicitud, podras publicarla desde tu panel.
            </p>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('inicio') }}" class="inline-flex min-h-12 items-center justify-center rounded-lg border border-gray-300 px-6 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-100">Cancelar</a>
                <button type="submit" class="eb-button px-8 py-3">Enviar solicitud</button>
            </div>
        </form>
    @endif
</div>
@endsection
