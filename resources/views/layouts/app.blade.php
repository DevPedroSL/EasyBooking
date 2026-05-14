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
        <nav class="app-nav sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
          <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex min-h-16 items-center justify-between gap-3 md:grid md:grid-cols-[1fr_auto_1fr] md:gap-4">
              <a href="{{ route('inicio') }}" class="min-w-0 justify-self-start">
                <div class="flex min-w-0 items-center gap-2">
                  <div class="brand-mark shrink-0 p-2">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 11-4.243 4.243 3 3 0 014.243-4.243z"></path></svg>
                  </div>
                  <span class="brand-name truncate text-lg font-black tracking-tight sm:text-xl">Easy<span>Booking</span></span>
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

              <div class="flex shrink-0 items-center gap-2 justify-self-end sm:gap-3">
                @guest
                  <div class="hidden md:flex items-center gap-3">
                    <a href="{{ route('login') }}" class="nav-link-button text-sm font-medium transition-colors">Iniciar Sesión</a>
                    <a href="{{ route('register') }}" class="nav-link-button text-sm font-medium transition-colors">Registrarse</a>
                  </div>
                @endguest

                @auth
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
                    <button id="user-menu-button" type="button" class="flex max-w-[11rem] items-center gap-2 text-sm font-medium sm:max-w-[14rem]" onclick="toggleDropdown()" aria-haspopup="true" aria-expanded="false" aria-controls="user-dropdown">
                      <img class="h-8 w-8 shrink-0 rounded-full border border-violet-200 object-cover" src="{{ auth()->user()->avatar_url }}" alt="Perfil">
                      <span class="hidden truncate sm:inline">{{ auth()->user()->name }}</span>
                      <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div id="user-dropdown" class="user-dropdown hidden absolute right-0 mt-2 w-56 max-w-[calc(100vw-2rem)] shadow-lg z-50">
                      <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm">Editar Perfil</a>
                      <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm">
                          Cerrar Sesión
                        </button>
                      </form>
                    </div>
                  </div>
                @endauth

                <button
                  type="button"
                  class="mobile-nav-toggle md:hidden"
                  @click="mobileMenuOpen = !mobileMenuOpen"
                  :aria-expanded="mobileMenuOpen.toString()"
                  aria-controls="mobile-nav-menu"
                  aria-label="Abrir navegación"
                >
                  <svg x-show="!mobileMenuOpen" class="h-5 w-5" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"></path></svg>
                  <svg x-show="mobileMenuOpen" class="h-5 w-5" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
              </div>
            </div>

            <div id="mobile-nav-menu" class="mobile-nav-menu md:hidden" x-show="mobileMenuOpen" x-cloak @click.outside="mobileMenuOpen = false">
              <a href="{{ route('inicio') }}" class="mobile-nav-link">Explorar</a>

              @auth
                <a href="{{ route('appointments.my') }}" class="mobile-nav-link">Mis Citas</a>

                @if($ownedBarbershop)
                  <a href="{{ route('barbershops.dashboard') }}" class="mobile-nav-link mobile-nav-link--featured">Gestionar mi barbería</a>
                @elseif(auth()->user()->role !== 'admin')
                  <a href="{{ route('barbershop-requests.create') }}" class="mobile-nav-link mobile-nav-link--featured">Crear nueva barbería</a>
                @endif

                @if(auth()->user()->role === 'admin')
                  <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link mobile-nav-link--featured">Administrar sitio</a>
                @endif
              @endauth

              @guest
                <a href="{{ route('login') }}" class="mobile-nav-link">Iniciar Sesión</a>
                <a href="{{ route('register') }}" class="mobile-nav-link mobile-nav-link--featured">Registrarse</a>
              @endguest
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
