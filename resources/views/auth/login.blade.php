@extends('layouts.app')

@section('title', 'Iniciar Sesion - EasyBooking')

@section('content')
<div class="page-shell max-w-2xl">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Iniciar sesion</h1>
            <p class="page-subtitle">Accede a tu cuenta para gestionar tus citas o continuar con una reserva pendiente.</p>
        </div>
    </div>

    <div class="eb-panel p-8">
        @if (session('status'))
            <div class="mb-4 rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm font-medium text-violet-800">
                {{ session('status') }}
            </div>
        @endif

        @if(session()->has('pending_appointment'))
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                Tu cita seleccionada se ha guardado. En cuanto inicies sesion, te llevaremos a la confirmacion.
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-bold text-gray-900">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                >
                @error('email')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="mb-2">
                    <label for="password" class="block text-sm font-bold text-gray-900">Contrasena</label>
                </div>

                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                >
                @error('password')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-2">
                @if (Route::has('password.request'))
                    <a
                        href="{{ route('password.request') }}"
                        class="text-sm font-semibold text-violet-700 transition hover:text-violet-900"
                    >
                         Has olvidado tu contrasena?
                    </a>
                    @endif
            </div>

            <label for="remember_me" class="flex items-center gap-3 text-sm font-medium text-gray-700">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                >
                <span>Recordarme</span>
            </label>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-600">
                    No tienes cuenta?
                    <a href="{{ route('register') }}" class="font-semibold text-violet-700 transition hover:text-violet-900">
                        Registrate
                    </a>
                </p>

                <button type="submit" class="eb-button px-6 py-3">
                    Iniciar sesion
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
