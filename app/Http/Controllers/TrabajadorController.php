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
}
