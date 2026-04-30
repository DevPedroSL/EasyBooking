@extends('layouts.app')

@section('title', 'Editar Barbería')

@section('content')
<div class="page-shell max-w-4xl">
    <div class="eb-panel p-6">
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Editar Mi Barbería</h1>
                <p class="mt-2 text-sm text-gray-600">Aquí puedes actualizar la información general de tu barbería.</p>
            </div>

            <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4 md:max-w-sm">
                <p class="text-sm font-bold text-violet-900">Servicios</p>
                <p class="mt-2 text-sm text-violet-800">Gestiona tus servicios en una pantalla aparte para tener todo más ordenado.</p>
                <a href="{{ route('barbershops.services.index') }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-lg bg-violet-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-violet-800">
                    Editar servicios
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded border border-green-300 bg-green-100 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded border border-red-400 bg-red-100 p-4 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-400 bg-red-100 p-4 text-red-700">
                <ul class="list-disc ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('barbershops.updateMy') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la Barbería</label>
                <input type="text" name="name" id="name" value="{{ old('name', $barbershop->name) }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500" required>
                @error('name') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="Description" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="Description" id="Description" rows="4" maxlength="50" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500" required>{{ old('Description', $barbershop->Description) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Máximo 50 caracteres.</p>
                @error('Description') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700">Dirección</label>
                <input type="text" name="address" id="address" value="{{ old('address', $barbershop->address) }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500" required>
                @error('address') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $barbershop->phone) }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500" required>
                @error('phone') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="visibility" class="block text-sm font-medium text-gray-700">Visibilidad</label>
                <select name="visibility" id="visibility" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500" required>
                    <option value="public" @selected(old('visibility', $barbershop->visibility) === 'public')>Pública</option>
                    <option value="private" @selected(old('visibility', $barbershop->visibility) === 'private')>Privada</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Las barberías públicas aparecen en Explorar. Las privadas solo las ve su barbero y el admin.</p>
                @error('visibility') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label for="image" class="block text-sm font-medium text-gray-700">Imagen de la barbería</label>
                @if($barbershop->image_url)
                    <div class="mb-3 flex h-40 w-full items-center justify-center rounded-2xl bg-white p-4 shadow-sm">
                        <img src="{{ $barbershop->image_url }}" alt="{{ $barbershop->name }}" class="h-full w-full object-contain">
                    </div>
                    <label class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-red-700">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                        Quitar imagen actual
                    </label>
                @endif
                <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                <p class="mt-1 text-xs text-gray-500">Sube una nueva imagen para reemplazar la actual.</p>
                @error('image') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                @error('remove_image') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('barbershops.services.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-gray-200 px-4 py-2 text-sm font-bold text-gray-800 transition hover:bg-gray-300">
                    Ir a servicios
                </a>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-violet-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-violet-800">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
