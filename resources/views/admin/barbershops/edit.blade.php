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
    <form action="{{ route('admin.barbershops.update', $barbershop) }}" method="POST" enctype="multipart/form-data">
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

      <div class="mb-6">
        <label for="visibility" class="block text-sm font-bold text-gray-900 mb-2">Visibilidad</label>
        <select id="visibility" name="visibility" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-600 focus:border-transparent" required>
          <option value="public" @selected(old('visibility', $barbershop->visibility) === 'public')>Pública</option>
          <option value="private" @selected(old('visibility', $barbershop->visibility) === 'private')>Privada</option>
        </select>
        @error('visibility')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="image" class="block text-sm font-bold text-gray-900 mb-2">Foto principal</label>
        @if($barbershop->image_url)
          <div class="mb-3 rounded-2xl border border-violet-100 bg-white p-4 shadow-sm">
            <div class="flex h-40 w-full items-center justify-center overflow-hidden rounded-2xl bg-white">
              <img src="{{ $barbershop->image_url }}" alt="{{ $barbershop->name }}" class="h-full w-full object-cover">
            </div>
            <label class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-red-700">
              <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
              Quitar foto principal
            </label>
          </div>
        @endif
        <input type="file" id="image" name="image" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-600 focus:border-transparent">
        @error('image')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        @error('remove_image')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="gallery_images" class="block text-sm font-bold text-gray-900 mb-2">Fotos del carrusel</label>
        @if(count($barbershop->gallery_images) > 0)
          <div class="mb-4 grid gap-4 sm:grid-cols-2">
            @foreach($barbershop->gallery_images as $galleryImage)
              <div class="rounded-2xl border border-violet-100 bg-white p-4 shadow-sm">
                <div class="flex h-40 w-full items-center justify-center overflow-hidden rounded-2xl bg-white">
                  <img src="{{ $galleryImage['url'] }}" alt="{{ $barbershop->name }} carrusel {{ $loop->iteration }}" class="h-full w-full object-cover">
                </div>
                <label class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-red-700">
                  <input type="checkbox" name="remove_gallery_images[]" value="{{ $galleryImage['index'] }}" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500">
                  Quitar esta foto
                </label>
              </div>
            @endforeach
          </div>
        @endif
        <input type="file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-600 focus:border-transparent">
        @error('gallery_images')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        @error('gallery_images.*')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        @error('remove_gallery_images')
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
