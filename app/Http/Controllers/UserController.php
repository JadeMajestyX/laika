<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
public function index(Request $request)
{
    $usuario = auth()->user();

    // Solo usuarios activos con rol 'U'
    $query = User::with('mascotas')
                 ->where('is_active', true)
                 ->where('rol', 'U');

    // Filtro por búsqueda de nombre o apellido (opcional)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('apellido_paterno', 'like', "%{$search}%")
              ->orWhere('apellido_materno', 'like', "%{$search}%");
        });
    }

    $usuarios = $query->paginate(10);

    return view('usuarios', compact('usuario', 'usuarios'));
}

}
