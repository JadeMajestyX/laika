<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TrabajadorController extends Controller
{
    public function index(){
        $usuario = auth()->user();
        $trabajadores = User::where('rol', '!=', 'U')->get();
        return view('trabajadores', compact('usuario', 'trabajadores'));
    }

    public function show($id){
        $trabajador = User::findOrFail($id);
        return view('trabajadores.show', compact('trabajador'));
    }

    public function create(){
        return view('trabajadores.create');
    }

    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users',
            'fecha_nacimiento' => 'required|date',
            'password' => 'required|min:6|confirmed',
        ]);

        User::create([
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'rol' => 'A', // Se crea como Administrador
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('trabajadores')->with('success', 'Administrador registrado correctamente.');
    }

    public function edit($id){
        $trabajador = User::findOrFail($id);
        return view('trabajadores.edit', compact('trabajador'));
    }

    public function update(Request $request, $id){
        $trabajador = User::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users,email,'.$trabajador->id,
            'fecha_nacimiento' => 'required|date',
        ]);

        $trabajador->update($request->all());

        return redirect()->route('trabajadores')->with('success', 'Trabajador actualizado correctamente.');
    }

    public function destroy($id){
        $trabajador = User::findOrFail($id);
        $trabajador->delete();
        return redirect()->route('trabajadores')->with('success', 'Trabajador eliminado correctamente.');
    }

    /**
     * Devuelve JSON paginado de trabajadores (roles distintos de 'U').
     * Parámetros soportados:
     *  - page, per_page
     *  - q: búsqueda en nombre y apellidos
     *  - rol: filtrar por rol específico (A, V, G)
     *  - scope: today | past (fecha de creación hoy o anteriores)
     *  - from, to: rango de fechas de creación (YYYY-MM-DD)
     */
    public function getTrabajadoresJson(Request $request)
    {
        $perPage = (int)$request->input('per_page', 10);
        if ($perPage <= 0) { $perPage = 10; }

        $query = User::query()->where('rol', '!=', 'U');

        if ($rol = $request->input('rol')) {
            $query->where('rol', $rol); // A, V, G
        }

        if ($search = $request->input('q')) {
            $query->where(function($q) use ($search) {
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

        $from = $request->input('from');
        $to = $request->input('to');
        if ($from) { $query->whereDate('created_at', '>=', $from); }
        if ($to) { $query->whereDate('created_at', '<=', $to); }

        $trabajadores = $query->paginate($perPage);

        $trabajadores->getCollection()->transform(function($t){
            return [
                'id' => $t->id,
                'nombre' => $t->nombre,
                'apellido_paterno' => $t->apellido_paterno,
                'apellido_materno' => $t->apellido_materno,
                'rol' => $t->rol,
                'email' => $t->email,
                'telefono' => $t->telefono,
                'fecha_nacimiento' => $t->fecha_nacimiento,
                'created_at' => $t->created_at?->toDateTimeString(),
                'clinica' => $t->clinica?->nombre,
            ];
        });

        return response()->json([
            'current_page' => $trabajadores->currentPage(),
            'last_page' => $trabajadores->lastPage(),
            'total' => $trabajadores->total(),
            'per_page' => $trabajadores->perPage(),
            'data' => $trabajadores->items(),
        ]);
    }
}
