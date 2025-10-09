<?php

// use App\Http\Controllers\Dashboard;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrabajadorController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(EnsureUserHasRole::class.':A')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios');
    Route::get('/mascotas', [MascotaController::class, 'index'])->name('mascotas');
    Route::get('/trabajadores', [TrabajadorController::class, 'index'])->name('trabajadores');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



Route::get('/inventario', function () {
    return view('inventario');
})->name('inventario');


Route::get('/reportes', function () {
    return view('reportes');
})->name('reportes');

Route::get('/configuracion', function () {
    return view('configuracion');
})->name('configuracion');

Route::get('/login', function () {
    return view('login');
})->name('login');


require __DIR__.'/auth.php';
