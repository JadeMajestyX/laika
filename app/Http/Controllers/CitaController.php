<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;

class CitaController extends Controller
{
    public function index(){

$citas = Cita::with(['clinica', 'servicio', 'mascota', 'creador'])
             ->orderBy('fecha', 'desc')
             ->paginate(10);

                     $usuario = auth()->user();

        return view('citas', compact('citas', 'usuario'));
    }
}
