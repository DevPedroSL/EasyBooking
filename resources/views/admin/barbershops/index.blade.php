@extends('layouts.app')

@section('title', 'Gestionar Barberías - EasyBooking')

@section('content')

<div class="w-full py-10 px-4">
  <div class="mb-8">
    <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Gestionar Barberías</h1>
    <p class="text-gray-600">Administra todas las barberías del sistema</p>
  </div>

  @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
      <p class="text-green-800">{{ session('success') }}</p>
    </div>
  @endif

  <div class="bg-white rounded-lg shadow-md overflow-x-auto">
    <table class="w-full min-w-max">
      <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Nombre</th>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 hidden sm:table-cell">Descripción</th>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 hidden md:table-cell">Dirección</th>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 hidden lg:table-cell">Teléfono</th>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($barbershops as $barbershop)
          <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
            <td class="px-4 py-4 text-sm text-gray-900 font-medium">{{ $barbershop->name }}</td>
            <td class="px-4 py-4 text-sm text-gray-600 hidden sm:table-cell">{{ Str::limit($barbershop->Description, 50) }}</td>
            <td class="px-4 py-4 text-sm text-gray-600 hidden md:table-cell">{{ Str::limit($barbershop->address, 40) }}</td>
            <td class="px-4 py-4 text-sm text-gray-600 hidden lg:table-cell">{{ $barbershop->phone }}</td>
            <td class="px-4 py-4 text-sm flex flex-col sm:flex-row gap-2">
              <a href="{{ route('admin.barbershops.edit', $barbershop) }}" class="inline-flex justify-center items-center bg-indigo-600 text-white px-3 py-2 rounded-lg hover:bg-indigo-700 transition text-xs font-bold whitespace-nowrap">
                Editar
              </a>
              <form action="{{ route('admin.barbershops.destroy', $barbershop) }}" method="POST" class="inline-block w-full sm:w-auto" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta barbería?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full sm:w-auto bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition text-xs font-bold">
                  Eliminar
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-8 text-center text-gray-600">
              No hay barberías creadas aún.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
