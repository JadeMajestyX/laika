<?php

// use App\Http\Controllers\Dashboard;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(EnsureUserHasRole::class.':A')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/trabajadores', function () {
    return view('trabajadores');
})->name('trabajadores');

Route::get('/inventario', function () {
    return view('inventario');
})->name('inventario');

Route::get('/mascotas', function () {
    return view('mascotas');
})->name('mascotas');

Route::get('/reportes', function () {
    return view('reportes');
})->name('reportes');

Route::get('/configuracion', function () {
    return view('configuracion');
})->name('configuracion');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/usuarios', function () {
    return view('usuarios');
})->name('usuarios');

require __DIR__.'/auth.php';
