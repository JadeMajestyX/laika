<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Http\Request;

class MascotaController extends Controller
{
    public function index(){
        $usuario = auth()->user();

        $mascotas = Mascota::with('user')->paginate(10);

        return view('mascotas', compact('usuario', 'mascotas'));
    }
}
