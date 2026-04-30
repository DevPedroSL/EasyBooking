<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BarbershopController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $name = trim((string) $request->query('name', ''));
    $address = trim((string) $request->query('address', ''));

    $barbershops = \App\Models\Barbershop::query()
        ->publiclyVisible()
        ->when($name !== '', function ($query) use ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        })
        ->when($address !== '', function ($query) use ($address) {
            $query->where('address', 'like', '%' . $address . '%');
        })
        ->get();

    return view('inicio', compact('barbershops', 'name', 'address'));
})->name('inicio');

Route::get('/inicio', function () {
    return redirect()->route('inicio');
});

Route::get('/barbershops/{barbershop}/image', [BarbershopController::class, 'image'])->name('barbershops.image');
Route::get('/users/{user}/avatar', [ProfileController::class, 'avatar'])->name('users.avatar');

Route::get('/barbershop/{name}', function ($name) {
    $barbershop = \App\Models\Barbershop::where('name', urldecode($name))->with('services', 'barber')->firstOrFail();

    abort_unless($barbershop->isVisibleTo(auth()->user()), 404);

    return view('barbershop', compact('barbershop'));
})->name('barbershop');

Route::get('/barbershop/{barbershop}/service/{service}/book', [App\Http\Controllers\AppointmentController::class, 'create'])->name('appointments.create');
Route::get('/barbershop/{barbershop}/appointments/confirm', [App\Http\Controllers\AppointmentController::class, 'confirm'])->name('appointments.confirm')->middleware('auth');
Route::post('/barbershop/{barbershop}/appointments', [App\Http\Controllers\AppointmentController::class, 'store'])->name('appointments.store')->middleware('auth');
Route::get('/my-appointments', [App\Http\Controllers\AppointmentController::class, 'my'])->name('appointments.my')->middleware('auth');
Route::get('/barber/appointments', [App\Http\Controllers\AppointmentController::class, 'barberAppointments'])->name('appointments.barber')->middleware('auth');
Route::get('/appointments/{appointment}', [App\Http\Controllers\AppointmentController::class, 'show'])->name('appointments.show')->middleware('auth');
Route::patch('/appointments/{appointment}/cancel', [App\Http\Controllers\AppointmentController::class, 'cancel'])->name('appointments.cancel')->middleware('auth');
Route::patch('/appointments/{appointment}/status', [App\Http\Controllers\AppointmentController::class, 'updateStatus'])->name('appointments.updateStatus')->middleware('auth');

Route::get('/dashboard', function () {
    return redirect()->route('inicio');
})->middleware(['auth', 'verified']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // User barbershop routes
    Route::get('/my-barbershop/create', [BarbershopController::class, 'create'])->name('barbershops.create');
    Route::post('/my-barbershop', [BarbershopController::class, 'store'])->name('barbershops.store');
    Route::get('/my-barbershop/edit', [BarbershopController::class, 'editMy'])->name('barbershops.editMy');
    Route::patch('/my-barbershop', [BarbershopController::class, 'updateMy'])->name('barbershops.updateMy');
    Route::get('/my-barbershop/services', [BarbershopController::class, 'servicesIndex'])->name('barbershops.services.index');
    Route::get('/my-barbershop/services/create', [BarbershopController::class, 'createService'])->name('barbershops.services.create');
    Route::post('/my-barbershop/services', [BarbershopController::class, 'storeService'])->name('barbershops.services.store');
    Route::get('/my-barbershop/services/{service}/edit', [BarbershopController::class, 'editService'])->name('barbershops.services.edit');
    Route::patch('/my-barbershop/services/{service}', [BarbershopController::class, 'updateService'])->name('barbershops.services.update');
    Route::delete('/my-barbershop/services/{service}', [BarbershopController::class, 'destroyService'])->name('barbershops.services.destroy');
});

// Admin routes for barbershops
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/barbershops', [AdminController::class, 'barbershopsIndex'])->name('barbershops.index');
    Route::get('/barbershops/{barbershop}/edit', [AdminController::class, 'barbershopsEdit'])->name('barbershops.edit');
    Route::patch('/barbershops/{barbershop}', [AdminController::class, 'barbershopsUpdate'])->name('barbershops.update');
    Route::delete('/barbershops/{barbershop}', [AdminController::class, 'barbershopsDestroy'])->name('barbershops.destroy');

    Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminController::class, 'usersEdit'])->name('users.edit');
    Route::patch('/users/{user}', [AdminController::class, 'usersUpdate'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'usersDestroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
