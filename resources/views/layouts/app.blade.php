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
    <body class="bg-gray-100">
        <nav class="bg-gray-50 border-b border-gray-100 sticky top-0 z-50">
  <div class="w-full px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16 items-center">
      <div class="flex items-center gap-2">
        <div class="bg-indigo-600 p-2 rounded-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 11-4.243 4.243 3 3 0 014.243-4.243z"></path></svg>
        </div>
        <span class="text-xl font-black text-gray-900 tracking-tight">Easy<span class="text-indigo-600">Booking</span></span>
      </div>

      <div class="hidden md:flex items-center space-x-6">
        <a href="{{ route('inicio') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Explorar</a>
        
        @guest
          <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Iniciar Sesión</a>
          <a href="{{ route('register') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Registrarse</a>
        @endguest
        
        @auth
          <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Mis Citas</a>
          <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Dashboard</a>
          @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.barbershops.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 font-bold">Gestionar Barberías</a>
          @endif
          <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Cerrar Sesión</a>
        @endauth
      </div>

      @auth
        <div class="flex items-center gap-4">
          <img class="h-8 w-8 rounded-full border border-gray-200" src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}" alt="Perfil">
        </div>
      @endauth
    </div>
  </div>
</nav>

<form id="logout-form-nav" method="POST" action="{{ route('logout') }}" style="display: none;">
  @csrf
</form>

        @yield('content')
    </body>
</html>
