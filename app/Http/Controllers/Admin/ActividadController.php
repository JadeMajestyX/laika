<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    public function index()
    {
        $actividades = Actividad::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

            //retorna sin vista por ahora
        return response()->json($actividades);
    }
}
