<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
        public function show($id)
        {
            $usuario = auth()->user();
            $cliente = User::with('mascotas')->findOrFail($id);

            return view('usuarios.show', compact('usuario', 'cliente'));
        }

        public function edit($id)
        {
            $usuario = auth()->user();
            $cliente = User::findOrFail($id);

            return view('usuarios.edit', compact('usuario', 'cliente'));
        }

        public function update(Request $request, $id)
        {
            $cliente = User::findOrFail($id);

            $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido_paterno' => 'nullable|string|max:255',
                'apellido_materno' => 'nullable|string|max:255',
                'email' => 'required|email',
                'rol' => 'required|string'
            ]);

            $cliente->update([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'rol' => $request->rol,
            ]);

            return redirect()->route('usuarios')->with('success', 'Usuario actualizado correctamente.');
        }

        public function destroy($id)
        {
            $cliente = User::findOrFail($id);
            $cliente->delete();

            return redirect()->route('usuarios')->with('success', 'Usuario eliminado correctamente.');
        }


}
