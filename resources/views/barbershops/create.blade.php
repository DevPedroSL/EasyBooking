@extends('layouts.app')

@section('title', 'Crear Barbería')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Crear Mi Barbería</h1>

    <form action="{{ route('barbershops.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la Barbería</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
            @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label for="Description" class="block text-sm font-medium text-gray-700">Descripción</label>
            <textarea name="Description" id="Description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>{{ old('Description') }}</textarea>
            @error('Description') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label for="address" class="block text-sm font-medium text-gray-700">Dirección</label>
            <input type="text" name="address" id="address" value="{{ old('address') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
            @error('address') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
            @error('phone') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Crear Barbería</button>
        </div>
    </form>
</div>
@endsection