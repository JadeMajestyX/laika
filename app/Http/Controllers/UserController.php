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

    /**
     * Endpoint JSON para listado paginado de clientes (usuarios con rol 'U' activos).
     * Soporta parámetros:
     *  - page (int)
     *  - per_page (int)
     *  - q (búsqueda por nombre/apellidos)
     *  - scope: today | past (creados hoy o anteriores)
     *  - from, to: rango de fechas de creación (YYYY-MM-DD)
     */
    public function getUsuariosJson(Request $request)
    {
        $perPage = (int)($request->input('per_page', 10));
        if ($perPage <= 0) { $perPage = 10; }

        $query = User::query()
            ->where('is_active', true)
            ->where('rol', 'U');

        // Búsqueda libre (nombre y apellidos)
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%");
            });
        }

        $today = now()->toDateString();
        $scope = $request->input('scope', 'today');
        if ($scope === 'today') {
            $query->whereDate('created_at', $today);
        } elseif ($scope === 'past') {
            $query->whereDate('created_at', '<', $today);
        }

        // Filtro por rango explícito si se proporciona
        $from = $request->input('from');
        $to = $request->input('to');
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $usuarios = $query->paginate($perPage);

        // Transformar colección para exponer solo campos necesarios
        $usuarios->getCollection()->transform(function ($u) {
            return [
                'id' => $u->id,
                'nombre' => $u->nombre,
                'apellido_paterno' => $u->apellido_paterno,
                'apellido_materno' => $u->apellido_materno,
                'genero' => $u->genero,
                'fecha_nacimiento' => $u->fecha_nacimiento,
                'email' => $u->email,
                'telefono' => $u->telefono,
                'created_at' => $u->created_at?->toDateTimeString(),
            ];
        });

        return response()->json([
            'current_page' => $usuarios->currentPage(),
            'last_page' => $usuarios->lastPage(),
            'total' => $usuarios->total(),
            'per_page' => $usuarios->perPage(),
            'data' => $usuarios->items(),
        ]);
    }
        public function show($id)
        {
            $usuario = auth()->user();
            $cliente = User::with('mascotas')->findOrFail($id);

            return response()->json($cliente);

        }

        public function edit($id)
        {
            $usuario = auth()->user();
            $cliente = User::findOrFail($id);

            return response()->json($cliente);


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
                'telefono' => $request->telefono,
                'genero' => $request->genero,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'clinica_id' => $request->clinica_id,
                'is_active' => $request->is_active ?? 0
            ]);

               return response()->json([
                    'success' => true,
                    'message' => 'Usuario actualizado correctamente.'
                ]);
            }

        public function destroy($id)
        {
            $cliente = User::findOrFail($id);
            $cliente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente.'
            ]);
        }

    }
