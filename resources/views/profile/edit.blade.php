@extends('layouts.app')

@section('title', 'Editar Perfil')

@section('content')
<div class="page-shell max-w-5xl">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Editar Perfil</h1>
        </div>
    </div>

    <div class="space-y-6">
        <section class="eb-panel p-8">
            <div class="mb-6">
                <h2 class="mt-2 text-2xl font-black text-gray-900">Información de la cuenta</h2>            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PATCH')

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="avatar" class="block text-sm font-bold text-gray-900 mb-2">Imagen de perfil</label>
                        <div class="flex items-center gap-4">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-2xl border border-violet-200 object-cover shadow-sm">
                            <div class="flex-1">
                                <input
                                    id="avatar"
                                    name="avatar"
                                    type="file"
                                    accept="image/*"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                                >
                                <p class="mt-2 text-xs text-gray-500">Sube una imagen JPG, PNG o WebP de hasta 2 MB.</p>
                            </div>
                        </div>
                        @error('avatar')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-bold text-gray-900 mb-2">Nombre</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
                            required
                            autocomplete="name"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                        >
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-900 mb-2">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="username"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-bold text-gray-900 mb-2">Teléfono</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="{{ old('phone', $user->phone) }}"
                            required
                            autocomplete="tel"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="20"
                            data-numeric-input
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                        >
                        @error('phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <button type="submit" class="eb-button px-6 py-3">Guardar cambios</button>
                    @if (session('status') === 'profile-updated')
                        <p class="text-sm font-medium text-violet-700">Perfil actualizado correctamente.</p>
                    @endif
                </div>
            </form>
        </section>

        <section class="eb-panel p-8">
            <div class="mb-6">
                <h2 class="mt-2 text-2xl font-black text-gray-900">Cambiar contraseña</h2>            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="current_password" class="block text-sm font-bold text-gray-900 mb-2">Contraseña actual</label>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                        >
                        @if ($errors->updatePassword->has('current_password'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-gray-900 mb-2">Nueva contraseña</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                        >
                        @if ($errors->updatePassword->has('password'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-gray-900 mb-2">Confirmar contraseña</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                        >
                        @if ($errors->updatePassword->has('password_confirmation'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <button type="submit" class="eb-button px-6 py-3">Actualizar contraseña</button>
                    @if (session('status') === 'password-updated')
                        <p class="text-sm font-medium text-violet-700">Contraseña actualizada correctamente.</p>
                    @endif
                </div>
            </form>
        </section>

        <section class="eb-panel p-8">
            <h2 class="mt-2 text-2xl font-black text-gray-900">{{ $user->name }}</h2>
            <div class="mt-4">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-28 w-28 rounded-3xl border border-violet-200 object-cover shadow-sm">
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-2 text-sm text-gray-700">
                <div>
                    <p class="font-bold text-gray-500">Rol</p>
                    <p class="mt-1">{{ ucfirst($user->role) }}</p>
                </div>
                <div>
                    <p class="font-bold text-gray-500">Email</p>
                    <p class="mt-1 break-all">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="font-bold text-gray-500">Teléfono</p>
                    <p class="mt-1">{{ $user->phone }}</p>
                </div>
                <div>
                    <p class="font-bold text-gray-500">Miembro desde</p>
                    <p class="mt-1">{{ $user->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </section>

        <section class="eb-panel border border-red-200 p-8">
            <h2 class="mt-2 text-2xl font-black text-gray-900">Eliminar cuenta</h2>

            <form
                method="POST"
                action="{{ route('profile.destroy') }}"
                class="mt-6 space-y-4"
                data-confirm-title="Eliminar cuenta"
                data-confirm-message="Vas a eliminar tu cuenta y todos sus datos asociados. Esta accion no se puede deshacer."
                data-confirm-button="Eliminar cuenta"
            >
                @csrf
                @method('DELETE')

                <div>
                    <label for="delete_password" class="block text-sm font-bold text-gray-900 mb-2">Confirma con tu contraseña</label>
                    <input
                        id="delete_password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-100"
                    >
                    @if ($errors->userDeletion->has('password'))
                        <p class="mt-2 text-sm text-red-600">{{ $errors->userDeletion->first('password') }}</p>
                    @endif
                </div>

                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-red-600 px-5 py-3 font-bold text-white transition hover:bg-red-700">
                    Eliminar cuenta
                </button>
            </form>
        </section>
    </div>
</div>
@endsection
