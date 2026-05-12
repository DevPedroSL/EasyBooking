@extends('layouts.app')

@section('title', 'Panel de Administración')

@section('content')
<div class="page-shell">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Panel de Administración</h1>
        </div>
    </div>

    <div class="space-y-6">
        <section class="eb-panel p-8">
            <h2 class="mt-2 text-3xl font-black text-gray-900">Barberías</h2>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('admin.barbershops.index') }}" class="eb-button px-6 py-3">Gestionar barberías</a>
                <a href="{{ route('admin.barbershop-requests.index') }}" class="eb-button px-6 py-3">Solicitudes pendientes ({{ $pendingBarbershopRequestsCount }})</a>
            </div>
        </section>

        <section class="eb-panel p-8">
            <h2 class="mt-2 text-3xl font-black text-gray-900">Usuarios</h2>
            <a href="{{ route('admin.users.index') }}" class="eb-button mt-6 px-6 py-3">Gestionar usuarios</a>
        </section>

        <section class="eb-panel p-8">
            <div class="mb-6">
                <h2 class="mt-2 text-2xl font-black text-gray-900">Datos generales</h2>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="eb-card">
                    <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Barberías</p>
                    <p class="mt-3 text-4xl font-black text-gray-900">{{ $barbershopsCount }}</p>
                </div>

                <div class="eb-card">
                    <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Usuarios</p>
                    <p class="mt-3 text-4xl font-black text-gray-900">{{ $usersCount }}</p>
                </div>

                <div class="eb-card">
                    <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Barberos</p>
                    <p class="mt-3 text-4xl font-black text-gray-900">{{ $barbersCount }}</p>
                </div>

                <div class="eb-card">
                    <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Clientes</p>
                    <p class="mt-3 text-4xl font-black text-gray-900">{{ $customersCount }}</p>
                </div>

                <div class="eb-card">
                    <p class="text-sm font-bold uppercase tracking-wide text-gray-500">Solicitudes pendientes</p>
                    <p class="mt-3 text-4xl font-black text-gray-900">{{ $pendingBarbershopRequestsCount }}</p>
                </div>
            </div>
        </section>
        <section class="eb-panel p-8">
            <h2 class="mt-2 text-3xl font-black text-gray-900">Copia de seguridad</h2>
            <div class="mt-6 space-y-4">
                <div class="flex flex-wrap gap-4">
                    <p class="text-gray-600 w-full">Descarga una copia de seguridad de la base de datos.</p>
                    <form method="POST" action="{{ route('admin.backup.database') }}">
                        @csrf
                        <button type="submit" class="eb-button mt-2">Descargar base de datos</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('admin.backup.database.restore') }}" enctype="multipart/form-data" class="space-y-4 border-t border-gray-200 pt-6">
                    @csrf
                    <div>
                        <label for="database_backup" class="block text-sm font-bold uppercase tracking-wide text-gray-500">Restaurar base de datos</label>
                        <input
                            id="database_backup"
                            name="database_backup"
                            type="file"
                            accept=".sql,text/plain,application/sql"
                            required
                            class="mt-3 block w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900"
                        >
                        @error('database_backup')
                            <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <label class="flex items-start gap-3 text-sm font-semibold text-gray-700">
                        <input type="checkbox" name="confirm_restore" value="1" required class="mt-1">
                        <span>Confirmo que quiero importar este archivo SQL en la base de datos actual.</span>
                    </label>
                    @error('confirm_restore')
                        <p class="text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="eb-button">Importar base de datos</button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
