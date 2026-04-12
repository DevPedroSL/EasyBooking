@extends('layouts.app')

@section('title', 'Editar Barbería - EasyBooking')

@section('content')

<div class="w-full py-10 px-4 max-w-2xl">
  <div class="mb-8">
    <a href="{{ route('admin.barbershops.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold mb-4 inline-block">← Volver</a>
    <h1 class="text-4xl font-extrabold text-gray-900">Editar Barbería</h1>
  </div>

  <div class="bg-white rounded-lg shadow-md p-8">
    <form action="{{ route('admin.barbershops.update', $barbershop) }}" method="POST">
      @csrf
      @method('PATCH')

      <div class="mb-6">
        <label for="name" class="block text-sm font-bold text-gray-900 mb-2">Nombre de la Barbería</label>
        <input type="text" id="name" name="name" value="{{ old('name', $barbershop->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent" required>
        @error('name')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="Description" class="block text-sm font-bold text-gray-900 mb-2">Descripción</label>
        <textarea id="Description" name="Description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent" required>{{ old('Description', $barbershop->Description) }}</textarea>
        @error('Description')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="address" class="block text-sm font-bold text-gray-900 mb-2">Dirección</label>
        <input type="text" id="address" name="address" value="{{ old('address', $barbershop->address) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent" required>
        @error('address')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="phone" class="block text-sm font-bold text-gray-900 mb-2">Teléfono</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $barbershop->phone) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent" required>
        @error('phone')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex gap-4">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition font-bold">
          Guardar Cambios
        </button>
        <a href="{{ route('admin.barbershops.index') }}" class="bg-gray-300 text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-400 transition font-bold">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>

@endsection
