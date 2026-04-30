@csrf

@if ($errors->any())
    <div class="mb-4 rounded border border-red-400 bg-red-100 p-4 text-red-700">
        <ul class="list-disc ps-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-4">
    <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Servicio</label>
    <input type="text" name="name" id="name" value="{{ old('name', $service->name ?? '') }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500" required>
    @error('name') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
    <textarea name="description" id="description" rows="3" maxlength="50" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500">{{ old('description', $service->description ?? '') }}</textarea>
    <p class="mt-1 text-xs text-gray-500">Máximo 50 caracteres.</p>
    @error('description') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="duration" class="block text-sm font-medium text-gray-700">Duración (minutos)</label>
        <input type="number" name="duration" id="duration" value="{{ old('duration', $service->duration ?? '') }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500" min="1" required>
        @error('duration') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="price" class="block text-sm font-medium text-gray-700">Precio (€)</label>
        <input type="number" name="price" id="price" value="{{ old('price', $service->price ?? '') }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500" min="0" step="0.01" required>
        @error('price') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-4">
    <label for="visibility" class="block text-sm font-medium text-gray-700">Visibilidad</label>
    <select name="visibility" id="visibility" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500" required>
        <option value="public" @selected(old('visibility', $service->visibility ?? 'public') === 'public')>Pública</option>
        <option value="private" @selected(old('visibility', $service->visibility ?? 'public') === 'private')>Privada</option>
    </select>
    <p class="mt-1 text-xs text-gray-500">Los servicios públicos se muestran a los clientes. Los privados solo los ve su barbero y el admin.</p>
    @error('visibility') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
</div>

<div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('barbershops.services.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-gray-200 px-4 py-2 text-sm font-bold text-gray-800 transition hover:bg-gray-300">
        Cancelar
    </a>
    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-violet-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-violet-800">
        {{ $submitLabel }}
    </button>
</div>
