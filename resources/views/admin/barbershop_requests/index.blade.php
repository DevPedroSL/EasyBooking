@extends('layouts.app')

@section('title', 'Solicitudes de barberia - EasyBooking')

@section('content')
<div class="page-shell">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Solicitudes de barberia</h1>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="eb-button px-5 py-3">Volver al panel</a>
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

    <div class="eb-panel overflow-x-auto">
        <table class="w-full min-w-max">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Barberia</th>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Usuario</th>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 hidden md:table-cell">Contacto</th>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Estado</th>
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr class="border-b border-gray-200 align-top hover:bg-gray-50">
                        <td class="px-4 py-4 text-sm">
                            <p class="font-bold text-gray-900">{{ $request->name }}</p>
                            <p class="mt-1 max-w-sm text-gray-600">{{ $request->address }}</p>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <p class="font-bold text-gray-900">{{ $request->requester?->name ?? 'Usuario eliminado' }}</p>
                            <p class="mt-1 text-gray-600">{{ $request->requester?->email }}</p>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600 hidden md:table-cell">
                            {{ $request->phone }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                ];
                                $statusLabels = [
                                    'pending' => 'Pendiente',
                                    'approved' => 'Aceptada',
                                    'rejected' => 'Rechazada',
                                ];
                            @endphp
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses[$request->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$request->status] ?? ucfirst($request->status) }}
                            </span>
                            @if($request->reviewed_at)
                                <p class="mt-2 text-xs text-gray-500">{{ $request->reviewed_at->format('d/m/Y H:i') }}</p>
                            @endif
                            @if($request->rejection_reason)
                                <p class="mt-2 max-w-xs text-xs font-semibold text-red-700">{{ $request->rejection_reason }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm">
                            @if($request->status === 'pending')
                                <div class="flex flex-col gap-2 xl:flex-row xl:items-center">
                                    <form action="{{ route('admin.barbershop-requests.approve', $request) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex min-h-8 items-center justify-center rounded-md bg-green-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-green-700">
                                            Aceptar
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.barbershop-requests.reject', $request) }}" method="POST" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                        @csrf
                                        @method('PATCH')
                                        <input name="rejection_reason" maxlength="1000" placeholder="Motivo opcional" class="h-8 w-40 rounded-md border border-gray-300 px-2 text-xs shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200">
                                        <button type="submit" class="inline-flex min-h-8 items-center justify-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-red-700">
                                            Rechazar
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs font-semibold text-gray-500">Revisada por {{ $request->reviewer?->name ?? 'admin' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-600">
                            No hay solicitudes de barberia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
