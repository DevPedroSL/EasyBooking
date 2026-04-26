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
            <a href="{{ route('admin.barbershops.index') }}" class="eb-button mt-6 px-6 py-3">Gestionar barberías</a>
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
            </div>
        </section>
    </div>
</div>
@endsection
