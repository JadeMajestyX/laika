<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horario;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        // Obtén el id de la clínica según tu modelo de usuario; aquí se asume $usuario->clinica_id
        $clinicaId = $usuario->clinica_id ?? null;

        if ($clinicaId) {
            $horarios = Horario::where('clinica_id', $clinicaId)
                        ->orderByRaw("FIELD(dia_semana, 'lunes','martes','miércoles','jueves','viernes','sábado','domingo')")
                        ->get();
        } else {
            $horarios = Horario::orderBy('id')->get();
        }

        return view('configuracion', compact('usuario', 'horarios'));
    }

    public function updateHorarios(Request $request)
    {
        // Esperamos un array 'horarios' con elementos {id, hora_inicio, hora_fin, activo}
        $data = $request->input('horarios', []);

        if (!is_array($data)) {
            return response()->json(['message' => 'Formato inválido'], 422);
        }

        $errors = [];
        foreach ($data as $i => $h) {
            $validator = Validator::make($h, [
                'id' => 'required|integer|exists:horarios,id',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'activo' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                $errors[$i] = $validator->errors()->all();
                continue;
            }

            // Actualizar registro
            $horario = Horario::find($h['id']);
            $horario->hora_inicio = $h['hora_inicio'];
            $horario->hora_fin = $h['hora_fin'];
            // Si hay 'activo' en DB, actualízalo; si no, lo ignoramos
            //if (array_key_exists('activo', $h) && \Schema::hasColumn('horarios', 'activo')) {
             //   $horario->activo = (bool)$h['activo'];
          //  }
            $horario->save();
        }

        if (!empty($errors)) {
            return response()->json(['message' => 'Errores de validación', 'errors' => $errors], 422);
        }

        return response()->json(['message' => 'Horarios actualizados correctamente']);
    }
}
