@extends('layouts.app')

@section('title', 'Gestionar Usuarios')

@section('content')
<div class="page-shell">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Gestionar Usuarios</h1>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="eb-button px-5 py-3">Volver al panel</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-violet-200 bg-violet-50 px-4 py-3 text-sm font-medium text-violet-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="eb-panel overflow-x-auto">
        <table class="w-full min-w-max">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Nombre</th>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Email</th>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 hidden md:table-cell">Teléfono</th>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Rol</th>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 hidden lg:table-cell">Barbería</th>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                        <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-4 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-4 text-sm text-gray-600 hidden md:table-cell">{{ $user->phone }}</td>
                        <td class="px-4 py-4 text-sm text-gray-600">{{ ucfirst($user->role) }}</td>
                        <td class="px-4 py-4 text-sm text-gray-600 hidden lg:table-cell">{{ $user->barbershop?->name ?? 'Sin barbería' }}</td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center justify-center rounded-lg bg-violet-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-violet-800">
                                    Editar
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-lg bg-red-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-red-700">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-600">No hay usuarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
