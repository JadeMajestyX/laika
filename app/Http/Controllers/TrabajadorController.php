<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class TrabajadorController extends Controller
{
    public function index(){
        $usuario = auth()->user();

        $trabajadores = User::where('rol', '!=', 'U')->paginate(10);

        return view('trabajadores', compact('usuario', 'trabajadores'));
    }
}
