@extends('layouts.app')

@section('title', 'Gestionar Barberías - EasyBooking')

@section('content')
<div class="page-shell">
  <div class="page-heading">
    <div>
      <h1 class="page-title">Gestionar Barberías</h1>    </div>
    <a href="{{ route('admin.dashboard') }}" class="eb-button px-5 py-3">Volver al panel</a>
  </div>

  @if(session('success'))
    <div class="mb-6 rounded-lg border border-violet-200 bg-violet-50 p-4">
      <p class="text-violet-800">{{ session('success') }}</p>
    </div>
  @endif

  <div class="eb-panel overflow-x-auto">
    <table class="w-full min-w-max">
      <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Nombre</th>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 hidden sm:table-cell">Barbero</th>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 hidden md:table-cell">Dirección</th>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 hidden lg:table-cell">Teléfono</th>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Visibilidad</th>
          <th class="px-4 py-3 text-left text-sm font-bold text-gray-900">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($barbershops as $barbershop)
          <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
            <td class="px-4 py-4 text-sm text-gray-900 font-medium">{{ $barbershop->name }}</td>
            <td class="px-4 py-4 text-sm text-gray-600 hidden sm:table-cell">{{ $barbershop->barber?->name ?? 'Sin asignar' }}</td>
            <td class="px-4 py-4 text-sm text-gray-600 hidden md:table-cell">{{ Str::limit($barbershop->address, 40) }}</td>
            <td class="px-4 py-4 text-sm text-gray-600 hidden lg:table-cell">{{ $barbershop->phone }}</td>
            <td class="px-4 py-4 text-sm">
              <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $barbershop->visibility === 'public' ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-700' }}">
                {{ $barbershop->visibility === 'public' ? 'Pública' : 'Privada' }}
              </span>
            </td>
            <td class="px-4 py-4 text-sm flex flex-col sm:flex-row gap-2">
              <a href="{{ route('admin.barbershops.edit', $barbershop) }}" class="inline-flex justify-center items-center bg-violet-700 text-white px-3 py-2 rounded-lg hover:bg-violet-800 transition text-xs font-bold whitespace-nowrap">
                Editar
              </a>
              <form
                action="{{ route('admin.barbershops.destroy', $barbershop) }}"
                method="POST"
                class="inline-block w-full sm:w-auto"
                data-confirm-title="Eliminar barberia"
                data-confirm-message="Vas a eliminar esta barberia. Esta accion no se puede deshacer."
                data-confirm-button="Eliminar barberia"
              >
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
            <td colspan="6" class="px-6 py-8 text-center text-gray-600">
              No hay barberías creadas aún.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
