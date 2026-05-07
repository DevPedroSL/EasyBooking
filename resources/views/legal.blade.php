@extends('layouts.app')

@section('title', 'Privacidad y Términos - EasyBooking')

@section('content')
<div class="page-shell max-w-3xl">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Privacidad y Términos</h1>
            <p class="page-subtitle">Información clara sobre el uso de la plataforma, la privacidad y las condiciones generales de EasyBooking.</p>
        </div>
    </div>

    <div class="eb-panel space-y-8 p-8 text-slate-700">
        <section class="space-y-3">
            <h2 class="text-xl font-black text-slate-900">1. Uso de la plataforma</h2>
            <p class="leading-7">
                EasyBooking permite consultar barberías, ver servicios disponibles y gestionar reservas de forma sencilla. Al utilizar la plataforma, aceptas hacer un uso correcto del sitio y respetar su funcionamiento.
            </p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-black text-slate-900">2. Datos y privacidad</h2>
            <p class="leading-7">
                Los datos que el usuario facilita se utilizan para gestionar citas, mejorar la experiencia dentro de la aplicación y permitir el funcionamiento normal del servicio. EasyBooking tratará esta información con medidas razonables de seguridad y únicamente para fines relacionados con la plataforma.
            </p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-black text-slate-900">3. Reservas y disponibilidad</h2>
            <p class="leading-7">
                La disponibilidad mostrada en EasyBooking depende de la información configurada por cada barbería. Aunque la plataforma intenta mantener los horarios actualizados, pueden producirse cambios por decisiones del negocio o por incidencias técnicas puntuales.
            </p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-black text-slate-900">4. Responsabilidad del servicio</h2>
            <p class="leading-7">
                Cada barbería es responsable de la información que publica, de sus horarios y de la atención prestada al cliente. EasyBooking actúa como plataforma de gestión y no como prestador directo del servicio de barbería.
            </p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-black text-slate-900">5. Cambios y contacto</h2>
            <p class="leading-7">
                EasyBooking puede actualizar estas condiciones cuando sea necesario para mejorar la plataforma o adaptarse a cambios legales o funcionales. Si tienes dudas sobre la privacidad, el uso de la aplicación o estas condiciones, puedes contactar con nosotros desde la sección correspondiente.
            </p>
        </section>

        <div class="pt-2">
            <a href="{{ route('inicio') }}" class="eb-button px-6 py-3">Volver al inicio</a>
        </div>
    </div>
</div>
@endsection
