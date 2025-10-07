<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
    })->name('dashboard');

Route::get('/usuarios', function () {
    return view('usuarios');
    })->name('usuarios');


Route::get('/mascotas', function () {
    return view('mascotas');
    })->name('mascotas');

Route::get('/inventario', function () {
    return view('inventario');
    })->name('inventario');

Route::get('/trabajadores', function () {
    return view('trabajadores');
    })->name('trabajadores');

Route::get('/reportes', function () {
    return view('reportes');
    })->name('reportes');

Route::get('/configuracion', function () {
    return view('configuracion');
    })->name('configuracion');


