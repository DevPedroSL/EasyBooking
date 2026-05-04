@extends('layouts.app')

@section('title', 'Editar Barberia')

@section('content')
<style>
    .barbershop-edit-page {
        display: grid;
        gap: 1.5rem;
    }

    .barbershop-edit-hero,
    .barbershop-edit-card,
    .barbershop-edit-sidebar-card {
        background: var(--eb-surface);
        border: 1px solid var(--eb-line);
        border-radius: 24px;
        box-shadow: 0 16px 32px rgba(24, 33, 30, 0.08);
    }

    .barbershop-edit-hero {
        padding: 2rem;
        background:
            radial-gradient(circle at top right, rgba(143, 106, 216, 0.18), transparent 18rem),
            linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(244, 240, 251, 0.98));
    }

    .barbershop-edit-hero-grid {
        display: grid;
        gap: 1rem;
        margin-top: 1.75rem;
    }

    .barbershop-edit-stat,
    .barbershop-edit-action {
        border: 1px solid rgba(143, 106, 216, 0.18);
        border-radius: 20px;
        padding: 1.25rem;
        background: rgba(255, 255, 255, 0.92);
    }

    .barbershop-edit-action {
        background: #f6f1ff;
    }

    .barbershop-edit-body {
        display: grid;
        gap: 1.5rem;
    }

    .barbershop-edit-card,
    .barbershop-edit-sidebar-card {
        padding: 1.5rem;
    }

    .barbershop-edit-section + .barbershop-edit-section {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(143, 106, 216, 0.14);
    }

    .barbershop-edit-fields {
        display: grid;
        gap: 1rem;
    }

    .barbershop-edit-gallery {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr));
    }

    .barbershop-edit-preview {
        overflow: hidden;
        border-radius: 20px;
        border: 1px solid rgba(143, 106, 216, 0.16);
        background: #f8f4ff;
        padding: 0.8rem;
    }

    .barbershop-edit-preview-frame {
        height: 180px;
        width: 100%;
        overflow: hidden;
        border-radius: 16px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .barbershop-edit-preview img {
        max-width: 100%;
        max-height: 100%;
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .barbershop-edit-placeholder {
        text-align: center;
        color: #6d5d8f;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .barbershop-edit-sidebar-list {
        display: grid;
        gap: 0.75rem;
    }

    .barbershop-edit-sidebar-item {
        border-radius: 16px;
        background: #f7f2ff;
        padding: 0.9rem 1rem;
    }

    @media (min-width: 768px) {
        .barbershop-edit-hero-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .barbershop-edit-fields {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

</style>

<div class="page-shell max-w-6xl">
    <div class="barbershop-edit-page">
        <section class="barbershop-edit-hero">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-violet-700">Mi barberia</p>
            <h1 class="mt-3 text-4xl font-black text-gray-900">Editar datos generales</h1>
            <p class="mt-3 max-w-3xl text-base leading-7 text-gray-700">
                Actualiza la informacion principal de tu barberia, controla su visibilidad y revisa la imagen que vera el cliente.
            </p>

            <div class="barbershop-edit-hero-grid">
                <div class="barbershop-edit-action">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">Servicios</p>
                    <p class="mt-3 text-2xl font-black text-gray-900">Gestion separada</p>
                    <p class="mt-2 text-sm leading-6 text-gray-700">
                        Administra servicios, precios, duraciones e imagenes desde su panel especifico.
                    </p>
                    <a href="{{ route('barbershops.services.index') }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-violet-700 px-5 py-3 text-sm font-black text-white transition hover:bg-violet-800">
                        Ir a servicios
                    </a>
                </div>
            </div>
        </section>

        @if(session('success'))
            <div class="rounded-2xl border border-green-300 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-red-300 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-300 bg-red-50 px-5 py-4 text-red-700">
                <p class="text-sm font-black uppercase tracking-[0.16em]">Revisa estos campos</p>
                <ul class="mt-3 list-disc ps-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="barbershop-edit-body">
            <form action="{{ route('barbershops.updateMy') }}" method="POST" enctype="multipart/form-data" class="barbershop-edit-card">
                @csrf
                @method('PATCH')

                <div class="barbershop-edit-section">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">Identidad</p>
                    <h2 class="mt-2 text-2xl font-black text-gray-900">Como se presenta tu barberia</h2>

                    <div class="barbershop-edit-fields mt-6">
                        <div style="grid-column: 1 / -1;">
                            <label for="name" class="mb-2 block text-sm font-bold text-gray-900">Nombre de la barberia</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $barbershop->name) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                            @error('name') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <label for="Description" class="mb-2 block text-sm font-bold text-gray-900">Descripcion</label>
                            <textarea name="Description" id="Description" rows="4" maxlength="50" class="w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>{{ old('Description', $barbershop->Description) }}</textarea>
                            <p class="mt-2 text-xs font-medium text-gray-500">Maximo 50 caracteres.</p>
                            @error('Description') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="barbershop-edit-section">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">Contacto</p>
                    <h2 class="mt-2 text-2xl font-black text-gray-900">Informacion visible para clientes</h2>

                    <div class="barbershop-edit-fields mt-6">
                        <div>
                            <label for="address" class="mb-2 block text-sm font-bold text-gray-900">Direccion</label>
                            <input type="text" name="address" id="address" value="{{ old('address', $barbershop->address) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                            @error('address') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="mb-2 block text-sm font-bold text-gray-900">Telefono</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $barbershop->phone) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                            @error('phone') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div style="grid-column: 1 / -1;">
                            <label for="visibility" class="mb-2 block text-sm font-bold text-gray-900">Visibilidad</label>
                            <select name="visibility" id="visibility" class="w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200" required>
                                <option value="public" @selected(old('visibility', $barbershop->visibility) === 'public')>Publica</option>
                                <option value="private" @selected(old('visibility', $barbershop->visibility) === 'private')>Privada</option>
                            </select>
                            <p class="mt-2 text-xs font-medium text-gray-500">Las barberias publicas aparecen en Explorar. Las privadas solo las ve su barbero y el admin.</p>
                            @error('visibility') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="barbershop-edit-section">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">Imagen</p>
                    <h2 class="mt-2 text-2xl font-black text-gray-900">Foto principal y carrusel</h2>

                    <div class="mt-6">
                        <p class="mb-3 text-sm font-bold text-gray-900">Foto principal</p>
                        @if($barbershop->image_url)
                            <div class="barbershop-edit-preview">
                                <div class="barbershop-edit-preview-frame">
                                    <img src="{{ $barbershop->image_url }}" alt="{{ $barbershop->name }}">
                                </div>
                                <label class="mt-3 inline-flex items-center gap-3 text-sm font-semibold text-red-700">
                                    <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <span>Quitar foto principal</span>
                                </label>
                            </div>
                        @else
                            <div class="barbershop-edit-preview">
                                <div class="barbershop-edit-preview-frame">
                                    <div class="barbershop-edit-placeholder">Todavía no has subido una foto principal.</div>
                                </div>
                            </div>
                        @endif

                        <label for="image" class="mt-5 mb-2 block text-sm font-bold text-gray-900">Cambiar foto principal</label>
                        <input type="file" name="image" id="image" accept="image/*" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200">
                        <p class="mt-2 text-xs font-medium text-gray-500">JPG, PNG o WebP de hasta 3 MB.</p>
                        @error('image') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        @error('remove_image') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror

                        <p class="mt-8 mb-3 text-sm font-bold text-gray-900">Fotos del carrusel</p>
                        @if(count($barbershop->gallery_images) > 0)
                            <div class="barbershop-edit-gallery">
                                @foreach($barbershop->gallery_images as $galleryImage)
                                    <div class="barbershop-edit-preview">
                                        <div class="barbershop-edit-preview-frame">
                                            <img src="{{ $galleryImage['url'] }}" alt="{{ $barbershop->name }} carrusel {{ $loop->iteration }}">
                                        </div>
                                        <label class="mt-3 inline-flex items-center gap-3 text-sm font-semibold text-red-700">
                                            <input type="checkbox" name="remove_gallery_images[]" value="{{ $galleryImage['index'] }}" class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                            <span>Quitar esta foto</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="barbershop-edit-preview">
                                <div class="barbershop-edit-preview-frame">
                                    <div class="barbershop-edit-placeholder">Todavía no has subido imágenes.</div>
                                </div>
                            </div>
                        @endif

                        <label for="gallery_images" class="mt-5 mb-2 block text-sm font-bold text-gray-900">Añadir fotos al carrusel</label>
                        <input type="file" name="gallery_images[]" id="gallery_images" accept="image/*" multiple class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200">
                        <p class="mt-2 text-xs font-medium text-gray-500">Hasta 4 imágenes de carrusel. JPG, PNG o WebP de hasta 3 MB cada una.</p>
                        @error('gallery_images') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        @error('gallery_images.*') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                        @error('remove_gallery_images') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="barbershop-edit-section">
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('barbershops.services.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-gray-300 px-6 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-100">
                            Gestionar servicios
                        </a>
                        <button type="submit" class="eb-button px-8 py-3">
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
