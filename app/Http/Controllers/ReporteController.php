<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index(){
        $usuario = auth()->user();

        return view('reportes', compact('usuario'));
    }
}
