<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Cita;
use App\Models\User;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AgendarCitaController extends Controller
{
    public function getClinicas(){
        $clinicas = Clinica::where("is_visible", true)->get();

        return json_encode($clinicas);
    }


    public function getServices(Request $request){
        $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id'
        ]);

        $clinica = Clinica::where('is_visible', true)
            ->where('id', $request->clinica_id)
            ->with('servicios')
            ->first();

        $servicios = $clinica ? $clinica->servicios : collect();
        return response()->json($servicios);
    }

    /**
     * POST /agendar-cita/disponibilidad
     * Devuelve lista de horarios disponibles (HH:MM) para una fecha y clínica.
     */
    public function disponibilidad(Request $request)
    {
        $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id',
            'servicio_id' => 'required|integer|exists:servicios,id',
            'fecha' => 'required|date_format:Y-m-d',
        ]);

        // Validar rango de fecha: hoy .. hoy + 1 año
        $hoy = Carbon::today();
        $limite = (clone $hoy)->addYear();
        $fechaSel = Carbon::createFromFormat('Y-m-d', $request->fecha);
        if ($fechaSel->lt($hoy) || $fechaSel->gt($limite)) {
            return response()->json(['availableTimes' => []]);
        }

        // Base de horarios (cada 30 min) 09:00 - 18:00
        $baseTimes = [];
        $start = Carbon::createFromFormat('H:i', '09:00');
        $end = Carbon::createFromFormat('H:i', '18:00');
        for ($t = $start->copy(); $t <= $end; $t->addMinutes(30)) {
            $baseTimes[] = $t->format('H:i');
        }

        // Obtener horas ocupadas en esa fecha para la clínica
        $ocupadas = Cita::where('clinica_id', $request->clinica_id)
            ->whereDate('fecha', $request->fecha)
            ->pluck('fecha')
            ->map(function($dt){ return Carbon::parse($dt)->format('H:i'); })
            ->unique()
            ->values()
            ->all();

        $available = array_values(array_diff($baseTimes, $ocupadas));

        // Si la fecha seleccionada es hoy, filtrar horas pasadas
        if ($fechaSel->equalTo($hoy)) {
            $nowTime = Carbon::now()->format('H:i');
            $available = array_values(array_filter($available, function($h) use ($nowTime) {
                return $h >= $nowTime;
            }));
        }

        return response()->json(['availableTimes' => $available]);
    }

    /**
     * POST /agendar-cita/reservar
     * Crea (o reutiliza) el usuario propietario, registra la mascota y crea la cita si el horario está libre.
     */
    public function reservar(Request $request)
    {
        $request->validate([
            'clinica_id' => 'required|integer|exists:clinicas,id',
            'servicio_id' => 'required|integer|exists:servicios,id',
            'owner_nombre' => 'required|string|max:100',
            'owner_apellido' => 'required|string|max:100',
            'owner_email' => 'required|email|max:150',
            'owner_telefono' => 'required|string|max:20',
            'mascota_nombre' => 'required|string|max:100',
            'mascota_especie' => 'required|string|max:100',
            'mascota_raza' => 'nullable|string|max:100',
            'fecha' => 'required|date_format:Y-m-d',
            'hora' => 'required|date_format:H:i',
        ]);

        $fechaHora = Carbon::createFromFormat('Y-m-d H:i', $request->fecha.' '.$request->hora);

        // Restringir a rango: ahora .. ahora + 1 año
        $now = Carbon::now();
        $limite = (clone $now)->addYear();
        if ($fechaHora->lt($now)) {
            return response()->json(['message' => 'La fecha/hora ya pasó'], 422);
        }
        if ($fechaHora->gt($limite)) {
            return response()->json(['message' => 'La fecha seleccionada excede el máximo de 1 año'], 422);
        }

        // Verificar disponibilidad (no exista otra cita a esa misma hora en la clínica)
        $yaOcupada = Cita::where('clinica_id', $request->clinica_id)
            ->where('fecha', $fechaHora)
            ->exists();
        if ($yaOcupada) {
            return response()->json(['message' => 'El horario seleccionado ya no está disponible'], 422);
        }

        // Crear o reutilizar usuario por email
        $user = User::where('email', $request->owner_email)->first();
        if (!$user) {
            $user = User::create([
                'nombre' => $request->owner_nombre,
                'apellido_paterno' => $request->owner_apellido,
                'email' => $request->owner_email,
                'telefono' => $request->owner_telefono,
                'password' => bcrypt(Str::random(12)),
            ]);
        } else {
            // Actualizar datos básicos
            $user->nombre = $request->owner_nombre;
            $user->apellido_paterno = $request->owner_apellido;
            $user->telefono = $request->owner_telefono;
            $user->save();
        }

        // Registrar mascota mínima
        $mascota = Mascota::create([
            'user_id' => $user->id,
            'nombre' => $request->mascota_nombre,
            'especie' => $request->mascota_especie,
            'raza' => $request->mascota_raza,
        ]);

        // Crear cita
        $cita = Cita::create([
            'clinica_id' => $request->clinica_id,
            'servicio_id' => $request->servicio_id,
            'mascota_id' => $mascota->id,
            'fecha' => $fechaHora,
            'status' => 'pendiente',
        ]);

        return response()->json([
            'message' => 'Cita reservada correctamente',
            'cita_id' => $cita->id,
        ]);
    }
}
