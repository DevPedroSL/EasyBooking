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

            @php($selectedScheduleDays = collect(old('schedule_days', [1, 2, 3, 4, 5]))->map(fn ($day) => (int) $day)->all())

            <div class="mb-6">
                <p class="block text-sm font-medium text-gray-700">Horario semanal</p>
                <p class="mt-1 text-xs text-gray-500">Marca los días que abrirá tu barbería y define la hora de apertura y cierre.</p>
                @error('schedule_days') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

                <div class="mt-3 space-y-3">
                    @foreach($weekdays as $day => $label)
                        <div class="rounded-md border border-gray-200 bg-gray-50 p-3">
                            <label class="flex items-center gap-3 text-sm font-semibold text-gray-800">
                                <input
                                    type="checkbox"
                                    name="schedule_days[]"
                                    value="{{ $day }}"
                                    class="h-4 w-4 rounded border-gray-300 text-violet-700 focus:ring-violet-500"
                                    @checked(in_array($day, $selectedScheduleDays, true))
                                >
                                <span>{{ $label }}</span>
                            </label>

                            <div class="mt-3 space-y-3">
                                @for($interval = 0; $interval < 2; $interval++)
                                    @php
                                        $defaultStartTime = $interval === 0 ? '10:00' : '';
                                        $defaultEndTime = $interval === 0 ? '20:00' : '';
                                    @endphp

                                    <div>
                                        <p class="text-xs font-bold text-gray-700">Tramo {{ $interval + 1 }}{{ $interval === 1 ? ' (opcional)' : '' }}</p>
                                        <div class="mt-2 grid gap-3 sm:grid-cols-2">
                                            <div>
                                                <label for="schedule_{{ $day }}_{{ $interval }}_start" class="block text-xs font-medium text-gray-600">Apertura</label>
                                                <input
                                                    type="time"
                                                    name="schedules[{{ $day }}][{{ $interval }}][start_time]"
                                                    id="schedule_{{ $day }}_{{ $interval }}_start"
                                                    value="{{ old("schedules.$day.$interval.start_time", $defaultStartTime) }}"
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500"
                                                >
                                                @error("schedules.$day.$interval.start_time") <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                                            </div>
                                            <div>
                                                <label for="schedule_{{ $day }}_{{ $interval }}_end" class="block text-xs font-medium text-gray-600">Cierre</label>
                                                <input
                                                    type="time"
                                                    name="schedules[{{ $day }}][{{ $interval }}][end_time]"
                                                    id="schedule_{{ $day }}_{{ $interval }}_end"
                                                    value="{{ old("schedules.$day.$interval.end_time", $defaultEndTime) }}"
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500"
                                                >
                                                @error("schedules.$day.$interval.end_time") <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-6">
                <label for="slot_interval_minutes" class="block text-sm font-medium text-gray-700">Frecuencia de citas</label>
                <select name="slot_interval_minutes" id="slot_interval_minutes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500">
                    <option value="15" @selected((int) old('slot_interval_minutes', 60) === 15)>Cada 15 minutos</option>
                    <option value="30" @selected((int) old('slot_interval_minutes', 60) === 30)>Cada 30 minutos</option>
                    <option value="45" @selected((int) old('slot_interval_minutes', 60) === 45)>Cada 45 minutos</option>
                    <option value="60" @selected((int) old('slot_interval_minutes', 60) === 60)>Cada 1 hora</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Define cada cuánto aparece un hueco disponible para reservar.</p>
                @error('slot_interval_minutes') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label for="image" class="block text-sm font-medium text-gray-700">Foto principal</label>
                <input type="file" name="image" id="image" accept="image/*" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500">
                <p class="mt-1 text-xs text-gray-500">La imagen principal que se verá arriba en la barbería. JPG, PNG o WebP de hasta 3 MB.</p>
                @error('image') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label for="gallery_images" class="block text-sm font-medium text-gray-700">Fotos del carrusel</label>
                <input type="file" name="gallery_images[]" id="gallery_images" accept="image/*" multiple class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-violet-500 focus:border-violet-500">
                <p class="mt-1 text-xs text-gray-500">Puedes subir hasta 4 imágenes para la galería inferior, de hasta 3 MB cada una.</p>
                @error('gallery_images') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                @error('gallery_images.*') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-violet-700 text-white px-4 py-2 rounded-md hover:bg-violet-800">Crear Barbería</button>
            </div>
        </form>
    </div>
</div>
@endsection
