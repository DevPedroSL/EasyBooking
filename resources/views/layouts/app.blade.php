<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'EasyBooking')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="eb-body">
        <nav class="app-nav sticky top-0 z-50">
  <div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="grid h-16 grid-cols-[1fr_auto_1fr] items-center gap-4">
      <a href="{{ route('inicio') }}" class="justify-self-start">
      <div class="flex items-center gap-2">
        <div class="brand-mark p-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 11-4.243 4.243 3 3 0 014.243-4.243z"></path></svg>
        </div>
        <span class="brand-name text-xl font-black tracking-tight">Easy<span>Booking</span></span>
      </div>
      </a>

      @auth
        @php($ownedBarbershop = auth()->user()->barbershop)
      @endauth

      <div class="hidden justify-self-center md:flex items-center justify-center gap-4">
        <a href="{{ route('inicio') }}" class="nav-link-button text-sm font-medium transition-colors">Explorar</a>

        @auth
          <a href="{{ route('appointments.my') }}" class="nav-link-button text-sm font-medium transition-colors">Mis Citas</a>
        @endauth
      </div>

      @guest
        <div class="hidden justify-self-end md:flex items-center gap-3">
          <a href="{{ route('login') }}" class="nav-link-button text-sm font-medium transition-colors">Iniciar Sesión</a>
          <a href="{{ route('register') }}" class="nav-link-button text-sm font-medium transition-colors">Registrarse</a>
        </div>
      @endguest

      @auth
        <div class="flex items-center gap-3 justify-self-end">
          <div class="hidden md:flex items-center gap-3">
            @if($ownedBarbershop)
              <a href="{{ route('barbershops.dashboard') }}" class="nav-action inline-block px-3 py-1 text-sm font-medium transition-colors">Gestionar mi barbería</a>
            @elseif(auth()->user()->role !== 'admin')
              <a href="{{ route('barbershop-requests.create') }}" class="nav-action inline-block px-3 py-1 text-sm font-medium transition-colors">Crear nueva barbería</a>
            @endif
            @if(auth()->user()->role === 'admin')
              <a href="{{ route('admin.dashboard') }}" class="nav-action inline-block px-3 py-1 text-sm font-medium transition-colors">Administrar sitio</a>
            @endif
          </div>

          <div class="relative">
            <button id="user-menu-button" class="flex items-center gap-2 text-sm font-medium" onclick="toggleDropdown()">
              <img class="h-8 w-8 rounded-full border border-violet-200 object-cover" src="{{ auth()->user()->avatar_url }}" alt="Perfil">
              <span>{{ auth()->user()->name }}</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div id="user-dropdown" class="user-dropdown hidden absolute right-0 mt-2 w-56 shadow-lg z-50">
              <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm">Editar Perfil</a>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left px-4 py-2 text-sm">
                  Cerrar Sesión
                </button>
              </form>
            </div>
          </div>
        </div>
      @endauth
    </div>
  </div>
</nav>

        <main class="app-main">
          @yield('content')
        </main>

        <footer class="app-footer">
          <div class="app-footer__inner">
            <p class="app-footer__brand">EasyBooking</p>

            <div class="app-footer__actions">
              <a href="{{ route('contact') }}" class="app-footer__button">Contáctanos</a>
              <a href="{{ route('legal') }}" class="app-footer__button">Privacidad, términos y uso</a>
            </div>
          </div>
        </footer>

        @include('layouts.partials.confirm-modal')
    </body>
</html>
