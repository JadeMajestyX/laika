<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horario;        // modelo Horario
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    
     // Mostrar la vista de configuración con los horarios de la clínica del usuario.
     
    public function index()
    {
        $usuario = auth()->user();
        // obten el id de la clínica asociado al usuario (ajusta si tu usuario usa otro campo)
        $clinicaId = $usuario->clinica_id ?? null;

        if ($clinicaId) {

            $dias = ['lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
            foreach ($dias as $dia) {
                    \App\Models\Horario::firstOrCreate(
                [
                    'clinica_id' => $clinicaId,
                    'dia_semana' => $dia
                ],
                [
                    'hora_inicio' => '09:00',
                    'hora_fin' => '18:00',
                    'activo' => 1
                ]
            );
        }
            // ordenamos por días en orden lógico 
            $ordenDias = "FIELD(dia_semana, 'lunes','martes','miércoles','jueves','viernes','sábado','domingo')";
            $horarios = Horario::where('clinica_id', $clinicaId)
                        ->orderByRaw($ordenDias)
                        ->get();
        } else {
            // si no hay clinica asociada, devolvemos colección vacía (evita mostrar datos de otras clínicas)
            $horarios = collect();
        }

        // Si la tabla de horarios está vacía, crear los 7 días automáticamente
        if ($horarios->isEmpty()) {
            $dias = ['lunes','martes','miércoles','jueves','viernes','sábado','domingo'];

            foreach ($dias as $dia) {
                Horario::create([
                    'clinica_id'  => $clinicaId,
                    'dia_semana'  => $dia,
                    'hora_inicio' => '09:00',  // valor por defecto
                    'hora_fin'    => '18:00',  // valor por defecto
                    'activo'      => 1
                ]);
            }

            // recargar los horarios ya creados
            $horarios = Horario::where('clinica_id', $clinicaId)
                ->orderByRaw($ordenDias)
                ->get();
        }

        return view('configuracion', compact('usuario', 'horarios'));
    }

    
     //Actualizar los horarios (recibe un array 'horarios' desde el formulario o JSON).
     
    public function updateHorarios(Request $request)
    {
        $usuario = auth()->user();
        $clinicaId = $usuario->clinica_id ?? null;

        if (!$clinicaId) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Usuario no asociado a ninguna clínica.'], 403);
            }
            return redirect()->route('configuracion')->with('error', 'Usuario no asociado a ninguna clínica.');
        }

        // Esperamos un array asociativo: horarios[id] = [hora_inicio, hora_fin, activo]
        $payload = $request->input('horarios', []);

        // Si el payload viene como lista (AJAX), normalizamos a asociativo por id si es posible
        if (!is_array($payload)) {
            $payload = [];
        }

        $errors = [];

        foreach ($payload as $id => $h) {
            // Si el front envía objetos sin key id, tratamos de leer dia_semana
            if (!is_numeric($id)) {
                // intentar usar 'id' dentro del item
                if (isset($h['id']) && is_numeric($h['id'])) {
                    $id = (int)$h['id'];
                } else {
                    // si no hay id, intentar por dia_semana
                    $dia = $h['dia_semana'] ?? null;
                    if (!$dia) continue;
                    // buscar o crear
                    $horario = Horario::firstOrNew([
                        'clinica_id' => $clinicaId,
                        'dia_semana' => $dia
                    ]);
                }
            }

            if (!isset($horario)) {
                // buscar por id y clinica para evitar tocar otras clínicas
                $horario = Horario::where('id', $id)->where('clinica_id', $clinicaId)->first();
                // si no existe, saltar
                if (!$horario) continue;
            }

            // Validar formato de horas
            $horaInicio = $h['hora_inicio'] ?? null;
            $horaFin = $h['hora_fin'] ?? null;
            $activo = array_key_exists('activo', $h) ? (bool)$h['activo'] : ($horario->activo ?? true);

            $validator = Validator::make([
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
            ], [
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            ]);

            if ($validator->fails()) {
                $errors[$id] = $validator->errors()->all();
                // no hacemos continue para permitir otros registros; aquí sólo registramos error
                continue;
            }

            // Guardar
            $horario->hora_inicio = $horaInicio;
            $horario->hora_fin = $horaFin;
            $horario->activo = $activo ? 1 : 0;
            $horario->clinica_id = $clinicaId; // asegurar asociación
            $horario->save();

            // limpiar variable para la siguiente iteración
            unset($horario);
        }

        if (!empty($errors)) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Errores de validación', 'errors' => $errors], 422);
            }
            return redirect()->route('configuracion')->with('error', 'Algunos horarios no se actualizaron por formato inválido.');
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Horarios actualizados correctamente.']);
        }

        return redirect()->route('configuracion')->with('success', 'Horarios actualizados correctamente.');
    }
}
