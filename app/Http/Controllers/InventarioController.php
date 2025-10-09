<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index(){
        $usuario = auth()->user();

        return view('inventario', compact('usuario'));
    }
}
