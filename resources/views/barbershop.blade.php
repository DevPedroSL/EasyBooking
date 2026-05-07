@extends('layouts.app')

@section('title', $barbershop->name . ' - EasyBooking')

@section('content')
@php
  $visibleServices = $barbershop->services->filter(fn ($service) => $service->isVisibleTo(auth()->user()));
  $principalImage = $barbershop->image_url;
  $galleryImages = $barbershop->gallery_images;
  $heroImages = collect([]);

  if ($principalImage) {
    $heroImages->push([
      'url' => $principalImage,
      'label' => $barbershop->name,
    ]);
  }

  foreach ($galleryImages as $index => $galleryImage) {
    $heroImages->push([
      'url' => $galleryImage['url'],
      'label' => $barbershop->name . ' ' . ($index + 2),
    ]);
  }
@endphp

<style>
  .shop-detail-hero-layout {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .shop-detail-hero-copy {
    min-width: 0;
    flex: 1 1 auto;
  }

  .shop-detail-hero-visual {
    display: flex;
    justify-content: center;
    flex: 0 0 auto;
  }

  .shop-detail-hero-frame {
    position: relative;
    width: min(100%, 22rem);
    height: 22rem;
    overflow: hidden;
    border-radius: 1.5rem;
    background: rgba(11, 8, 22, 0.22);
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 18px 36px rgba(0, 0, 0, 0.26);
  }

  .shop-detail-hero-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .shop-detail-hero-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(36, 26, 56, 0.7);
    color: #fff;
    font-size: 1.25rem;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .shop-detail-hero-nav--prev {
    left: 1rem;
  }

  .shop-detail-hero-nav--next {
    right: 1rem;
  }

  .shop-gallery-thumbs {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(4.8rem, 1fr));
    gap: 0.6rem;
    margin-top: 0.85rem;
  }

  .shop-gallery-thumb {
    border: 2px solid transparent;
    border-radius: 0.9rem;
    overflow: hidden;
    background: #f8f4ff;
    opacity: 0.7;
  }

  .shop-gallery-thumb.is-active {
    border-color: rgba(103, 74, 157, 0.72);
    opacity: 1;
  }

  .shop-gallery-thumb img {
    display: block;
    width: 100%;
    height: 4.5rem;
    object-fit: cover;
  }

  @media (min-width: 900px) {
    .shop-detail-hero-layout {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }

    .shop-detail-hero-visual {
      justify-content: flex-end;
    }
  }
</style>

<div class="page-shell max-w-4xl">
  <div class="shop-hero mb-8">
    <div class="shop-detail-hero-layout">
      <div class="shop-detail-hero-copy">
        <h1 class="mb-6 text-4xl font-black">{{ $barbershop->name }}</h1>
        <div class="flex items-center gap-2 text-sm font-semibold">
          <x-heroicon-s-map-pin class="h-4 w-4" />
          <p class="font-semibold">{{ $barbershop->address }}</p>
        </div>
        <div class="mt-2 flex items-center gap-2 text-sm font-semibold">
          <x-heroicon-s-phone class="h-4 w-4" />
          <p class="font-semibold">{{ $barbershop->phone }}</p>
        </div>

        @if($barbershop->barber)
          <div class="mt-6 inline-flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-3 backdrop-blur-sm">
            <img src="{{ $barbershop->barber->avatar_url }}" alt="{{ $barbershop->barber->name }}" class="shop-avatar h-14 w-14 rounded-2xl object-cover">
            <div>
              <p class="text-lg font-bold">{{ $barbershop->barber->name }}</p>
            </div>
          </div>
        @endif
      </div>

      <div class="shop-detail-hero-visual">
        @if($heroImages->isNotEmpty())
          <div class="shop-detail-hero-frame" x-data="{ activeImage: 0, totalImages: {{ $heroImages->count() }} }" aria-label="{{ $barbershop->name }}">
            @foreach($heroImages as $heroImage)
              <img
                x-cloak
                x-show="activeImage === {{ $loop->index }}"
                src="{{ $heroImage['url'] }}"
                alt="{{ $heroImage['label'] }}"
                class="shop-detail-hero-image"
              >
            @endforeach

            @if($heroImages->count() > 1)
              <button type="button" class="shop-detail-hero-nav shop-detail-hero-nav--prev" @click="activeImage = (activeImage - 1 + totalImages) % totalImages" aria-label="Foto anterior">
                ‹
              </button>
              <button type="button" class="shop-detail-hero-nav shop-detail-hero-nav--next" @click="activeImage = (activeImage + 1) % totalImages" aria-label="Foto siguiente">
                ›
              </button>
            @endif
          </div>
        @else
          <div class="shop-detail-hero-frame flex items-center justify-center p-6 text-center shadow-2xl">
            <span class="text-2xl font-black text-violet-50">{{ $barbershop->name }}</span>
          </div>
        @endif
      </div>
    </div>
  </div>

  <div class="eb-panel overflow-hidden">
    <div class="p-8">
      <h2 class="text-2xl font-black text-gray-900 mb-6">Nuestros Servicios</h2>
      
      <div class="space-y-4">
        @forelse($visibleServices as $service)
          <div class="service-row flex flex-col gap-4 p-4 transition-colors group lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1">
              <h4 class="text-lg font-bold text-gray-800">{{ $service->name }}</h4>
              <p class="text-gray-500 text-sm">{{ \Illuminate\Support\Str::limit($service->description, 50) }}</p>
              <span class="text-violet-700 text-sm font-bold">{{ $service->duration }} min</span>
            </div>

            @if(count($service->image_urls) > 0)
              <div class="flex flex-wrap gap-3 lg:justify-center">
                @foreach($service->image_urls as $index => $imageUrl)
                  <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-sm">
                    <img src="{{ $imageUrl }}" alt="{{ $service->name }} {{ $index + 1 }}" class="h-full w-full object-cover">
                  </div>
                @endforeach
              </div>
            @endif

            <div class="ml-0 text-right lg:ml-4">
              <span class="block text-xl font-bold text-gray-900 mb-2">{{ $service->price }}€</span>
              <a href="{{ route('appointments.create', ['barbershop' => $barbershop, 'service' => $service]) }}" class="eb-button eb-button-gold text-sm">
                Reservar
              </a>
            </div>
          </div>
        @empty
          <p class="text-gray-600">No hay servicios visibles disponibles en este momento.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
