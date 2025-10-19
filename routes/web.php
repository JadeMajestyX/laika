<?php

// use App\Http\Controllers\Dashboard;

use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrabajadorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SearchController;



Route::get('/', function () {
    return view('welcome');
});

Route::middleware(EnsureUserHasRole::class.':A')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios');
    Route::get('/usuarios/{id}', [UserController::class, 'show'])->name('usuarios.show');
    Route::get('/usuarios/{id}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');
    Route::get('/citas', [App\Http\Controllers\CitaController::class, 'index'])->name('citas');

    Route::get('/mascotas', [MascotaController::class, 'index'])->name('mascotas');
    Route::get('/mascotas/{id}', [MascotaController::class, 'show'])->name('mascotas.show');
    Route::get('/mascotas/{id}/editar', [MascotaController::class, 'edit'])->name('mascotas.edit');
    Route::put('/mascotas/{id}', [MascotaController::class, 'update'])->name('mascotas.update');
    Route::delete('/mascotas/{id}', [MascotaController::class, 'destroy'])->name('mascotas.destroy');

    Route::get('/trabajadores', [TrabajadorController::class, 'index'])->name('trabajadores');
    Route::get('/trabajadores/{id}', [TrabajadorController::class, 'show'])->name('trabajadores.show');
    Route::get('/trabajadores/{id}/editar', [TrabajadorController::class, 'edit'])->name('trabajadores.edit');
    Route::put('/trabajadores/{id}', [TrabajadorController::class, 'update'])->name('trabajadores.update');
    Route::delete('/trabajadores/{id}', [TrabajadorController::class, 'destroy'])->name('trabajadores.destroy');

    // registrar nuevo administrador
    Route::get('/trabajadores/crear', [TrabajadorController::class, 'create'])->name('trabajadores.create');
    Route::post('/trabajadores', [TrabajadorController::class, 'store'])->name('trabajadores.store');

    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes');
    Route::get('/inventario', [App\Http\Controllers\InventarioController::class, 'index'])->name('inventario');
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion');

    //Route::get('/buscar', [SearchController::class, 'buscar'])->name('buscar');


    //obtener datos del dashboard
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/olvidar-password', function () {
    return view('auth.forgot-password');
})->name('password.request');



Route::get('/login', function () {
    return view('login');
})->name('login');


require __DIR__.'/auth.php';
