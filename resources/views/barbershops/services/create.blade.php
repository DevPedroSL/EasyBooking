@extends('layouts.app')

@section('title', 'Crear Servicio')

@section('content')
<div class="page-shell max-w-3xl">
    <div class="eb-panel p-6">
        <div class="mb-6">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-700">Servicios</p>
            <h1 class="mt-2 text-3xl font-black text-gray-900">Crear nuevo servicio</h1>
            <p class="mt-2 text-sm text-gray-600">Añade un servicio nuevo para {{ $barbershop->name }}.</p>
        </div>

        <form action="{{ route('barbershops.services.store') }}" method="POST">
            @include('barbershops.services._form', ['submitLabel' => 'Crear servicio'])
        </form>
    </div>
</div>
@endsection
