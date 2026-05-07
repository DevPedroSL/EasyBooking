@extends('layouts.app')

@section('title', 'Detalles de la Cita')

@section('content')
<div class="page-shell max-w-4xl">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Detalles de la Cita</h1>
        </div>
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

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    @php($canManageAsBarber = auth()->user()->barbershop && auth()->user()->barbershop->id === $appointment->barbershop_id)
    @php($barberComment = $appointment->barber_comment ?: $appointment->rejection_reason)

    <div class="eb-panel p-8">
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <p class="text-sm font-bold text-gray-500 uppercase">Cliente</p>
                <p class="mt-2 text-xl font-black text-gray-900">{{ $appointment->client->name }}</p>
                <p class="text-sm text-gray-600">{{ $appointment->client->email }}</p>
                <p class="text-sm text-gray-600">{{ $appointment->client->phone }}</p>
            </div>

            <div>
                <p class="text-sm font-bold text-gray-500 uppercase">Servicio</p>
                <p class="mt-2 text-xl font-black text-gray-900">{{ $appointment->service->name }}</p>
                <p class="text-sm text-gray-600">{{ $appointment->service->duration }} min</p>
            </div>

            <div>
                <p class="text-sm font-bold text-gray-500 uppercase">Fecha</p>
                <p class="mt-2 text-xl font-black text-gray-900">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</p>
            </div>

            <div>
                <p class="text-sm font-bold text-gray-500 uppercase">Hora</p>
                <p class="mt-2 text-xl font-black text-gray-900">
                    {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}
                </p>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-200 pt-6">
            <p class="text-sm font-bold text-gray-500 uppercase">Comentario del cliente</p>
            <p class="mt-3 text-gray-800 whitespace-pre-line break-words">{{ $appointment->client_comment ?: 'Sin comentario' }}</p>
        </div>

        @if(in_array($appointment->status, ['accepted', 'rejected'], true) && $barberComment)
            <div class="mt-8 border-t {{ $appointment->status === 'rejected' ? 'border-red-100' : 'border-violet-100' }} pt-6">
                <p class="text-sm font-bold {{ $appointment->status === 'rejected' ? 'text-red-600' : 'text-violet-700' }} uppercase">Comentario de la barbería</p>
                <p class="mt-3 text-gray-800 whitespace-pre-line break-words">{{ $barberComment }}</p>
            </div>
        @endif

        @if($canManageAsBarber && $appointment->status === 'pending')
            <div class="mt-8 border-t border-gray-200 pt-6">
                <p class="text-sm font-bold text-gray-500 uppercase">Gestionar cita</p>
                <form method="POST" action="{{ route('appointments.updateStatus', $appointment) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="barber_comment" class="block text-sm font-bold text-gray-700">Comentario para el cliente</label>
                        <textarea
                            id="barber_comment"
                            name="barber_comment"
                            rows="4"
                            maxlength="300"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100"
                            placeholder="Opcional"
                        >{{ old('barber_comment') }}</textarea>
                        @if($errors->has('barber_comment'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('barber_comment') }}</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" name="status" value="accepted" class="eb-button px-5 py-3">Aceptar cita</button>
                        <button
                            type="submit"
                            name="status"
                            value="rejected"
                            class="inline-flex min-h-10 items-center justify-center rounded-lg bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700"
                            data-confirm-title="Rechazar cita"
                            data-confirm-message="Vas a rechazar esta cita. El comentario que hayas escrito se mostrara al cliente."
                            data-confirm-button="Rechazar cita"
                        >
                            Rechazar cita
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="mt-8 flex flex-wrap justify-end gap-3">
            @if($appointment->client_id === auth()->id() && $appointment->status === 'pending')
                <form
                    method="POST"
                    action="{{ route('appointments.cancel', $appointment) }}"
                    data-confirm-title="Cancelar cita"
                    data-confirm-message="Vas a cancelar esta cita. Si quieres reservarla de nuevo, tendras que hacerlo otra vez."
                    data-confirm-button="Cancelar cita"
                >
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-red-600 px-5 py-3 font-bold text-white transition hover:bg-red-700">
                        Cancelar cita
                    </button>
                </form>
            @endif

            @if($canManageAsBarber)
                <a href="{{ route('appointments.barber') }}" class="eb-button px-6 py-3">Volver a citas</a>
            @else
                <a href="{{ route('appointments.my') }}" class="eb-button px-6 py-3">Volver a mis citas</a>
            @endif
        </div>
    </div>
</div>
@endsection
