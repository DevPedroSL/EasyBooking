@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="page-shell max-w-3xl">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Editar Usuario</h1>
        </div>

        <a href="{{ route('admin.users.index') }}" class="eb-button px-5 py-3">Volver a usuarios</a>
    </div>

    <div class="eb-panel p-8">
        @if(session('error'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="mb-2 block text-sm font-bold text-gray-900">Nombre</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label for="email" class="mb-2 block text-sm font-bold text-gray-900">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="mb-2 block text-sm font-bold text-gray-900">Teléfono</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" inputmode="numeric" pattern="[0-9]*" maxlength="20" data-numeric-input class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                    @error('phone')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="role" class="mb-2 block text-sm font-bold text-gray-900">Rol</label>
                <select id="role" name="role" class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                    @foreach(['admin' => 'Admin', 'barber' => 'Barber', 'customer' => 'Customer'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="is_banned" class="mb-2 block text-sm font-bold text-gray-900">Estado de la cuenta</label>
                <select id="is_banned" name="is_banned" class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200">
                    <option value="0" @selected((string) old('is_banned', (int) $user->is_banned) === '0')>Activa</option>
                    <option value="1" @selected((string) old('is_banned', (int) $user->is_banned) === '1')>Deshabilitada</option>
                </select>
                <p class="mt-2 text-sm text-gray-600">
                    Si deshabilitas la cuenta, el usuario no podrá iniciar sesión hasta que vuelva a ser activado.
                </p>
                @error('is_banned')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="eb-button px-6 py-3">Guardar cambios</button>
                <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-gray-200 px-6 py-3 font-bold text-gray-800 transition hover:bg-gray-300">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
