@extends('layouts.app')

@section('title', 'Mis Citas - ' . $barbershop->name)

@section('content')
<div class="page-shell page-shell-wide">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Citas de {{ $barbershop->name }}</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-violet-100 border border-violet-300 text-violet-800 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="eb-panel overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <h2 class="text-xl font-black text-gray-900">Citas</h2>

                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('appointments.barber') }}"
                        class="inline-flex min-h-10 items-center justify-center rounded-lg px-4 py-2 text-sm font-bold transition {{ $selectedStatus === null ? 'bg-violet-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        Todas
                    </a>

                    @foreach($statusOptions as $status => $label)
                        <a
                            href="{{ route('appointments.barber', ['status' => $status]) }}"
                            class="inline-flex min-h-10 items-center justify-center rounded-lg px-4 py-2 text-sm font-bold transition {{ $selectedStatus === $status ? 'bg-violet-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[82rem] w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Codigo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($appointments as $appointment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $appointment->client->name }}</div>
                            <div class="text-sm text-gray-500">{{ $appointment->client->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $appointment->service->name }}</div>
                            <div class="text-sm text-gray-500">{{ $appointment->service->duration }} min</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if($appointment->client_comment)
                                {{ \Illuminate\Support\Str::limit($appointment->client_comment, 15) }}
                            @else
                                Sin comentario
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($appointment->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($appointment->status === 'accepted') bg-violet-100 text-violet-800
                                @elseif($appointment->status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($appointment->status === 'pending') Pendiente
                                @elseif($appointment->status === 'accepted') Aceptada
                                @elseif($appointment->status === 'rejected') Rechazada
                                @else {{ $appointment->status }} @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($appointment->status === 'accepted')
                                <span class="rounded-lg bg-gray-100 px-3 py-2 font-mono font-bold tracking-wider text-gray-900">
                                    {{ $appointment->confirmation_code }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('appointments.show', $appointment) }}" class="text-violet-700 hover:text-violet-900">Ver detalles</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No hay citas {{ $selectedStatus ? strtolower($statusOptions[$selectedStatus]) : 'programadas' }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
