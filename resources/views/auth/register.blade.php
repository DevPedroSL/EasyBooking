@extends('layouts.app')

@section('title', 'Registrarse - EasyBooking')

@section('content')
<div class="page-shell max-w-2xl">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Crear cuenta</h1>
            <p class="page-subtitle">Registrate para reservar citas, gestionarlas y continuar con cualquier seleccion pendiente.</p>
        </div>
    </div>

    <div class="eb-panel p-8">
        @if(session()->has('pending_appointment'))
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                Tu cita seleccionada se ha guardado. Cuando termines el registro, podras confirmarla.
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="mb-2 block text-sm font-bold text-gray-900">Nombre</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                >
                @error('name')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-bold text-gray-900">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                >
                @error('email')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="mb-2 block text-sm font-bold text-gray-900">Telefono</label>
                <input
                    id="phone"
                    name="phone"
                    type="text"
                    value="{{ old('phone') }}"
                    required
                    autocomplete="tel"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                >
                @error('phone')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-bold text-gray-900">Contrasena</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                >
                @error('password')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-bold text-gray-900">Confirmar contrasena</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                >
                @error('password_confirmation')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-600">
                    Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="font-semibold text-violet-700 transition hover:text-violet-900">
                        Inicia sesion
                    </a>
                </p>

                <button type="submit" class="eb-button px-6 py-3">
                    Registrarse
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
