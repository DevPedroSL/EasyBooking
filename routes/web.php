<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BarbershopController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $barbershops = \App\Models\Barbershop::all();
    return view('inicio', compact('barbershops'));
})->name('inicio');

Route::get('/inicio', function () {
    return redirect()->route('inicio');
});

Route::get('/barbershop/{name}', function ($name) {
    $barbershop = \App\Models\Barbershop::where('name', urldecode($name))->with('services')->firstOrFail();
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
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
