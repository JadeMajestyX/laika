<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index(){
        $usuario = auth()->user();
        return view('configuracion', compact('usuario'));
    }
}
