<section class="rounded-xl bg-indigo-600 p-6 text-white">
    <p class="font-bold uppercase tracking-wide text-violet-200">EasyBooking</p>
    <h1>Comprobante de cita</h1>
    <p class="inline-flex rounded-full bg-green-100 px-3 py-1 font-bold text-green-800">Cita aceptada #{{ $appointment->id }}</p>
    <p>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }} · {{ $appointment->start_time->format('H:i') }} - {{ $appointment->end_time->format('H:i') }}</p>
</section>

<section class="rounded-xl border border-gray-200 bg-white p-5">
    <h2>Codigo de confirmación</h2>
    <p>{{ $codigo }}</p>
</section>

<section class="rounded-xl border border-gray-200 bg-white p-5">
    <h2>Cliente</h2>
    <p>Nombre: {{ $appointment->client->name }}</p>
    <p>Email: {{ $appointment->client->email }}</p>
    <p>Telefono: {{ $appointment->client->phone }}</p>
</section>

<section class="rounded-xl border border-gray-200 bg-white p-5">
    <h2>Barberia</h2>
    <p>Nombre: {{ $appointment->barbershop->name }}</p>
    <p>Direccion: {{ $appointment->barbershop->address }}</p>
    <p>Telefono: {{ $appointment->barbershop->phone }}</p>
</section>

<section class="rounded-xl border border-gray-200 bg-white p-5">
    <h2>Servicio</h2>
    <p>Nombre: {{ $appointment->service->name }}</p>
    <p>Duracion: {{ $appointment->service->duration }} min</p>
    <p>Precio: {{ number_format((float) $appointment->service->price, 2, ',', '.') }} EUR</p>
</section>

@if($appointment->client_comment)
    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <h2>Comentario del cliente</h2>
        <p>{{ $appointment->client_comment }}</p>
    </section>
@endif

@if($barberComment)
    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <h2>Comentario de la barberia</h2>
        <p>{{ $barberComment }}</p>
    </section>
@endif

<section class="text-sm text-gray-500">
    <p>Generado el {{ $generatedAt->format('d/m/Y H:i') }}</p>
</section>
