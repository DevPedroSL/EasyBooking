@extends('layouts.app')

@section('title', 'Crear Barbería')

@section('content')
<div class="page-shell max-w-2xl">
    <div class="p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6">Crear Mi Barbería</h1>

        <form action="{{ route('barbershops.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la Barbería</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500" required>
                @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="Description" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="Description" id="Description" rows="4" maxlength="50" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500" required>{{ old('Description') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Máximo 50 caracteres.</p>
                @error('Description') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700">Dirección</label>
                <input type="text" name="address" id="address" value="{{ old('address') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500" required>
                @error('address') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500" required>
                @error('phone') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label for="visibility" class="block text-sm font-medium text-gray-700">Visibilidad</label>
                <select name="visibility" id="visibility" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500" required>
                    <option value="public" @selected(old('visibility', 'public') === 'public')>Pública</option>
                    <option value="private" @selected(old('visibility') === 'private')>Privada</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Las barberías públicas aparecen en Explorar. Las privadas solo las ve su barbero y el admin.</p>
                @error('visibility') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label for="image" class="block text-sm font-medium text-gray-700">Imagen de la barbería</label>
                <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500">
                <p class="mt-1 text-xs text-gray-500">Puedes subir una imagen JPG, PNG o WebP de hasta 3 MB.</p>
                @error('image') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-violet-700 text-white px-4 py-2 rounded-md hover:bg-violet-800">Crear Barbería</button>
            </div>
        </form>
    </div>
</div>
@endsection
