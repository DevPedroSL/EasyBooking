@extends('layouts.app')

@section('title', 'Editar Barbería')

@section('content')
<div class="page-shell max-w-4xl">
<div class="eb-panel p-6">
    <h1 class="text-3xl font-black text-gray-900 mb-6">Editar Mi Barbería</h1>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('barbershops.updateMy') }}" method="POST">
        @csrf
        @method('PATCH')

        <fieldset class="mb-8 pb-8 border-b">
            <legend class="text-xl font-semibold text-gray-800 mb-4">Información de la Barbería</legend>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la Barbería</label>
                <input type="text" name="name" id="name" value="{{ old('name', $barbershop->name) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" required>
                @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="Description" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="Description" id="Description" rows="4" maxlength="50" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" required>{{ old('Description', $barbershop->Description) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Máximo 50 caracteres.</p>
                @error('Description') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700">Dirección</label>
                <input type="text" name="address" id="address" value="{{ old('address', $barbershop->address) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" required>
                @error('address') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $barbershop->phone) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" required>
                @error('phone') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>
        </fieldset>

        <fieldset class="mb-8">
            <legend class="text-xl font-semibold text-gray-800 mb-4">Servicios</legend>

            @forelse ($services as $service)
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="font-semibold text-gray-700">Servicio {{ $loop->iteration }}</h3>
                        <button
                            type="submit"
                            form="delete-service-{{ $service->id }}"
                            class="inline-flex items-center justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-bold text-white transition hover:bg-red-700"
                            onclick="return confirm('¿Seguro que quieres eliminar este servicio?');"
                        >
                            Eliminar servicio
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label for="services_{{ $service->id }}_name" class="block text-sm font-medium text-gray-700">Nombre del Servicio</label>
                        <input type="text" name="services[{{ $service->id }}][name]" id="services_{{ $service->id }}_name" value="{{ old("services.{$service->id}.name", $service->name) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" required>
                        @error("services.{$service->id}.name") <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="services_{{ $service->id }}_description" class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="services[{{ $service->id }}][description]" id="services_{{ $service->id }}_description" rows="2" maxlength="50" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500">{{ old("services.{$service->id}.description", $service->description) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Máximo 50 caracteres.</p>
                        @error("services.{$service->id}.description") <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="services_{{ $service->id }}_duration" class="block text-sm font-medium text-gray-700">Duración (minutos)</label>
                            <input type="number" name="services[{{ $service->id }}][duration]" id="services_{{ $service->id }}_duration" value="{{ old("services.{$service->id}.duration", $service->duration) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" min="1" required>
                            @error("services.{$service->id}.duration") <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="services_{{ $service->id }}_price" class="block text-sm font-medium text-gray-700">Precio (€)</label>
                            <input type="number" name="services[{{ $service->id }}][price]" id="services_{{ $service->id }}_price" value="{{ old("services.{$service->id}.price", $service->price) }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" min="0" step="0.01" required>
                            @error("services.{$service->id}.price") <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-600 italic mb-4">No tienes servicios registrados aún.</p>
            @endforelse

            <div class="mb-6 p-4 bg-violet-50 rounded-lg border border-violet-200">
                <h3 class="font-semibold text-violet-700 mb-4">Agregar Nuevo Servicio</h3>
                
                <div class="mb-3">
                    <label for="new_service_name" class="block text-sm font-medium text-gray-700">Nombre del Servicio</label>
                    <input type="text" name="new_services[0][name]" id="new_service_name" value="{{ old('new_services.0.name') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" placeholder="Ej: Corte de cabello" required>
                    @error("new_services.0.name") <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="mb-3">
                    <label for="new_service_description" class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="new_services[0][description]" id="new_service_description" rows="2" maxlength="50" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" placeholder="Describe el servicio...">{{ old('new_services.0.description') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Máximo 50 caracteres.</p>
                    @error("new_services.0.description") <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="new_service_duration" class="block text-sm font-medium text-gray-700">Duración (minutos)</label>
                        <input type="number" name="new_services[0][duration]" id="new_service_duration" value="{{ old('new_services.0.duration') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" min="1" placeholder="30" required>
                        @error("new_services.0.duration") <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="new_service_price" class="block text-sm font-medium text-gray-700">Precio (€)</label>
                        <input type="number" name="new_services[0][price]" id="new_service_price" value="{{ old('new_services.0.price') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-violet-500 focus:border-violet-500" min="0" step="0.01" placeholder="15.00" required>
                        @error("new_services.0.price") <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </fieldset>

        <div class="flex justify-end gap-2">
            <a href="{{ route('barbershops.editMy') }}" class="bg-gray-400 text-white px-4 py-2 rounded-md hover:bg-gray-500">Cancelar</a>
            <button type="submit" class="bg-violet-700 text-white px-4 py-2 rounded-md hover:bg-violet-800">Actualizar Barbería y Servicios</button>
        </div>
    </form>

    @foreach ($services as $service)
        <form id="delete-service-{{ $service->id }}" action="{{ route('barbershops.services.destroy', $service) }}" method="POST">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
</div>
</div>
@endsection
