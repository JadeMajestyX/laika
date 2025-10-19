<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\json;

class MascotaController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $mascotas = Mascota::with('user')->paginate(10);
        return view('mascotas', compact('usuario', 'mascotas'));
    }

    public function show($id)
    {
        $usuario = Auth::user();
        $mascota = Mascota::with('user')->findOrFail($id);
        return view('mascotas.show', compact('usuario', 'mascota'));
    }

    public function edit($id)
    {
        $usuario = Auth::user();
        $mascota = Mascota::findOrFail($id);
        return view('mascotas.edit', compact('usuario', 'mascota'));
    }

    public function update(Request $request, $id)
    {
        $mascota = Mascota::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'especie' => 'required|string|max:255',
            'raza' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'peso' => 'nullable|numeric',
        ]);

        $mascota->update([
            'nombre' => $request->nombre,
            'especie' => $request->especie,
            'raza' => $request->raza,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'peso' => $request->peso,
        ]);

        return redirect()->route('mascotas')->with('success', 'Mascota actualizada correctamente.');
    }

    public function destroy($id)
    {
        $mascota = Mascota::findOrFail($id);
        $mascota->delete();

        return redirect()->route('mascotas')->with('success', 'Mascota eliminada correctamente.');
    }

    public function getAllMascotas(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        if ($perPage < 5) $perPage = 5;
        if ($perPage > 50) $perPage = 50;

        $mascotas = Mascota::with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($mascotas);
    }
}

