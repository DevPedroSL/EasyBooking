@extends('layouts.app')

@section('title', 'Inicio - EasyBooking')

@section('content')

<section class="py-8 px-4 min-h-screen">
  <div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Peluquerías destacadas</h2>
        <p class="text-gray-500 text-sm">Los mejores profesionales cerca de ti</p>
      </div>
      <div class="w-full sm:w-auto">
        <label for="searchBarbershop" class="sr-only">Buscar barbería</label>
        <input id="searchBarbershop" type="search" placeholder="Buscar barbería por nombre..." class="w-full sm:w-80 px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
      </div>
    </div>
    
    <div id="barbershopGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
      @foreach($barbershops as $barbershop)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition">
          <div class="relative">
            <img src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=400&q=80" class="w-full h-32 object-cover">
          </div>
          <div class="p-3">
            <h3 class="font-bold text-gray-900 text-sm truncate">{{ $barbershop->name }}</h3>
            <p class="text-gray-500 text-xs mb-3">{{ $barbershop->address }}</p>
            <a href="{{ route('barbershop', urlencode($barbershop->name)) }}" class="block text-center border border-indigo-600 text-indigo-600 text-xs font-bold py-1.5 rounded-lg hover:bg-indigo-600 hover:text-white transition">Reservar</a>
          </div>
        </div>
      @endforeach

    </div>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchBarbershop');
    const cards = Array.from(document.querySelectorAll('#barbershopGrid > div'));

    if (!searchInput || cards.length === 0) {
      return;
    }

    searchInput.addEventListener('input', function () {
      const query = this.value.trim().toLowerCase();

      cards.forEach(function (card) {
        const name = card.querySelector('h3')?.textContent.trim().toLowerCase() || '';
        card.style.display = name.includes(query) ? '' : 'none';
      });
    });
  });
</script>

@endsection