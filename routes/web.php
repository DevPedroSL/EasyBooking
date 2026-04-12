<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BarbershopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('inicio');
});

Route::get('/inicio', function () {
    $barbershops = \App\Models\Barbershop::all();
    return view('inicio', compact('barbershops'));
})->name('inicio');

Route::get('/barbershop/{name}', function ($name) {
    $barbershop = \App\Models\Barbershop::where('name', urldecode($name))->with('services')->firstOrFail();
    return view('barbershop', compact('barbershop'));
})->name('barbershop');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes for barbershops
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/barbershops', [BarbershopController::class, 'index'])->name('barbershops.index');
    Route::get('/barbershops/{barbershop}/edit', [BarbershopController::class, 'edit'])->name('barbershops.edit');
    Route::patch('/barbershops/{barbershop}', [BarbershopController::class, 'update'])->name('barbershops.update');
    Route::delete('/barbershops/{barbershop}', [BarbershopController::class, 'destroy'])->name('barbershops.destroy');
});

require __DIR__.'/auth.php';
