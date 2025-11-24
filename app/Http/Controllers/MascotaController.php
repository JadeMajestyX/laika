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

    public function show(Request $request, $id)
    {
    $mascota = Mascota::with('user')->findOrFail($id);

    return response()->json($mascota); // ← SIEMPRE JSON PARA FECTH/MODALS
    }


    public function edit($id)

    {
    $mascota = Mascota::findOrFail($id);
    return response()->json($mascota);
    }


    public function update(Request $request, $id)
    {
        $mascota = Mascota::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'especie' => 'required|string|max:255',
            'raza' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|string',
            'peso' => 'nullable|numeric',
        ]);

        $mascota->update([
            'nombre' => $request->nombre,
            'especie' => $request->especie,
            'raza' => $request->raza,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo' => $request->sexo,
            'peso' => $request->peso,

              ]);
       
          return response()->json([
        'message' => 'Mascota actualizada correctamente.'
    ]);
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
        $perPage = max(5, min(50, $perPage));

        $scope = $request->get('scope', 'today'); // today | past
        $q = trim((string) $request->get('q', ''));
        $from = $request->get('from');
        $to = $request->get('to');

        $query = Mascota::with('user');

        // Filtro por fechas de registro (created_at)
        $hasDateFilter = false;
        if ($from) {
            try {
                $fromDate = \Carbon\Carbon::parse($from)->startOfDay();
                $query->where('created_at', '>=', $fromDate);
                $hasDateFilter = true;
            } catch (\Exception $e) { /* ignorar */ }
        }
        if ($to) {
            try {
                $toDate = \Carbon\Carbon::parse($to)->endOfDay();
                $query->where('created_at', '<=', $toDate);
                $hasDateFilter = true;
            } catch (\Exception $e) { /* ignorar */ }
        }
        if (!$hasDateFilter) {
            $today = \Carbon\Carbon::today();
            if ($scope === 'past') {
                $query->whereDate('created_at', '<', $today);
            } else { // today por defecto
                $query->whereDate('created_at', '=', $today);
            }
        }

        // Búsqueda por texto
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $like = '%' . str_replace(['%','_'], ['\%','\_'], $q) . '%';
                $sub->where('nombre', 'like', $like)
                    ->orWhere('especie', 'like', $like)
                    ->orWhere('raza', 'like', $like)
                    ->orWhereHas('user', function ($q2) use ($like) {
                        $q2->where('nombre', 'like', $like)
                           ->orWhere('apellido_paterno', 'like', $like)
                           ->orWhere('apellido_materno', 'like', $like);
                    });
            });
        }

        $mascotas = $query->orderByDesc('created_at')->paginate($perPage);
        return response()->json($mascotas);
    }
}

