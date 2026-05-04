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

<div class="mt-4">
    <label for="images" class="block text-sm font-medium text-gray-700">Imagenes del servicio</label>
    <input type="file" name="images[]" id="images" accept="image/*" multiple class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-violet-500 focus:ring-violet-500">
    <p class="mt-1 text-xs text-gray-500">Puedes subir hasta 3 imagenes. Maximo 3 MB por imagen.</p>
    @error('images') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
    @error('images.*') <p class="text-sm text-red-500">{{ $message }}</p> @enderror

    @if(isset($service) && count($service->image_urls) > 0)
        <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="mb-3 text-sm font-semibold text-gray-700">Imagenes actuales</p>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($service->image_urls as $index => $imageUrl)
                    <label class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
                        <div class="flex h-32 w-full items-center justify-center overflow-hidden rounded-xl bg-gray-50">
                            <img src="{{ $imageUrl }}" alt="{{ $service->name }} {{ $index + 1 }}" class="h-full w-full object-cover">
                        </div>

                        <span class="mt-3 inline-flex items-center gap-3 text-sm font-medium text-gray-700">
                            <input
                                type="checkbox"
                                name="remove_images[]"
                                value="{{ $index }}"
                                class="h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                            >
                            <span>Eliminar esta imagen</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>

<div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('barbershops.services.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-gray-200 px-4 py-2 text-sm font-bold text-gray-800 transition hover:bg-gray-300">
        Cancelar
    </a>
    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-violet-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-violet-800">
        {{ $submitLabel }}
    </button>
</div>
