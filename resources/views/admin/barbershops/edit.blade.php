@extends('layouts.app')

@section('title', 'Editar Barbería - EasyBooking')

@section('content')

<div class="page-shell max-w-3xl">
  <div class="page-heading">
    <div>
      <h1 class="page-title">Editar Barbería</h1>
    </div>
    <a href="{{ route('admin.barbershops.index') }}" class="eb-button px-5 py-3">Volver a barberías</a>
  </div>

  <div class="eb-panel p-8">
    <form action="{{ route('admin.barbershops.update', $barbershop) }}" method="POST">
      @csrf
      @method('PATCH')

      <div class="mb-6">
        <label for="name" class="block text-sm font-bold text-gray-900 mb-2">Nombre de la Barbería</label>
        <input type="text" id="name" name="name" value="{{ old('name', $barbershop->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-600 focus:border-transparent" required>
        @error('name')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="Description" class="block text-sm font-bold text-gray-900 mb-2">Descripción</label>
        <textarea id="Description" name="Description" rows="4" maxlength="50" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-600 focus:border-transparent" required>{{ old('Description', $barbershop->Description) }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Máximo 50 caracteres.</p>
        @error('Description')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="address" class="block text-sm font-bold text-gray-900 mb-2">Dirección</label>
        <input type="text" id="address" name="address" value="{{ old('address', $barbershop->address) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-600 focus:border-transparent" required>
        @error('address')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="phone" class="block text-sm font-bold text-gray-900 mb-2">Teléfono</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $barbershop->phone) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-600 focus:border-transparent" required>
        @error('phone')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex gap-4">
        <button type="submit" class="eb-button px-6 py-3">
          Guardar Cambios
        </button>
        <a href="{{ route('admin.barbershops.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-gray-300 px-6 py-3 font-bold text-gray-900 transition hover:bg-gray-400">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>

@endsection
