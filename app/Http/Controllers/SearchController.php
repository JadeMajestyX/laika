<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Mascota;

class SearchController extends Controller
{
    public function buscar(Request $request)
    {
        $query = $request->input('q');
        
        if (!$query) {      //redirige al dash
            return redirect()->route('dashboard');
        }

        // buscar en usuarios y mascotas
        $usuarios = User::where('nombre', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();

        $mascotas = Mascota::where('nombre', 'like', "%{$query}%")
            ->orWhere('raza', 'like', "%{$query}%")
            ->get();

        return view('resultados', compact('query', 'usuarios', 'mascotas'));
    }
}

