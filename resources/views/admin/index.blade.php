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
                    <p class="text-gray-600 w-full">Descarga una copia de seguridad completa de la base de datos y archivos del sistema.</p>
                    <form method="POST" action="{{ route('admin.backup') }}">
                        @csrf
                        <button type="submit" class="eb-button mt-2">Descargar copia completa</button>
                    </form>
                    <form method="POST" action="{{ route('admin.backup.database') }}">
                        @csrf
                        <button type="submit" class="eb-button mt-2">Descargar base de datos</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
