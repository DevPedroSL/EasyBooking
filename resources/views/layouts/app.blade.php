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
    <div class="flex justify-between h-16 items-center">
      <div class="flex items-center gap-2">
        <div class="brand-mark p-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 11-4.243 4.243 3 3 0 014.243-4.243z"></path></svg>
        </div>
        <span class="brand-name text-xl font-black tracking-tight">Easy<span>Booking</span></span>
      </div>

      <div class="hidden md:flex items-center space-x-6">
        <a href="{{ route('inicio') }}" class="text-sm font-medium">Explorar</a>
        
        @guest
          <a href="{{ route('login') }}" class="text-sm font-medium">Iniciar Sesión</a>
          <a href="{{ route('register') }}" class="text-sm font-medium">Registrarse</a>
        @endguest
        
        @auth
          <a href="{{ route('appointments.my') }}" class="text-sm font-medium">Mis Citas</a>
          @if(!auth()->user()->barbershop)
            <a href="{{ route('barbershops.create') }}" class="text-sm font-medium">Crear mi propia barbería</a>
          @else
            <a href="{{ route('barbershops.editMy') }}" class="text-sm font-medium">Gestionar mi barbería</a>
          @endif
          @if(auth()->user()->barbershop)
            <a href="{{ route('appointments.barber') }}" class="nav-action inline-block px-3 py-1 text-sm font-medium transition-colors">Gestionar Citas</a>
          @endif
          @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="nav-action inline-block px-3 py-1 text-sm font-medium transition-colors">Panel Admin</a>
          @endif
        @endauth
      </div>

      @auth
        <div class="relative">
          <button id="user-menu-button" class="flex items-center gap-2 text-sm font-medium" onclick="toggleDropdown()">
            <img class="h-8 w-8 rounded-full border border-violet-200" src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}" alt="Perfil">
            <span>{{ auth()->user()->name }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          <div id="user-dropdown" class="user-dropdown hidden absolute right-0 mt-2 w-48 shadow-lg z-50">
            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm">Editar Perfil</a>
            @if(auth()->user()->role === 'admin')
              <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm">Panel Admin</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="block w-full text-left px-4 py-2 text-sm">
                Cerrar Sesión
              </button>
            </form>
          </div>
        </div>
      @endauth
    </div>
  </div>
</nav>

        <main class="app-main">
          @yield('content')
        </main>

        <script>
        function toggleDropdown() {
          const dropdown = document.getElementById('user-dropdown');
          dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
          const dropdown = document.getElementById('user-dropdown');
          const button = document.getElementById('user-menu-button');
          if (button && !button.contains(event.target)) {
            dropdown.classList.add('hidden');
          }
        });
        </script>
    </body>
</html>
