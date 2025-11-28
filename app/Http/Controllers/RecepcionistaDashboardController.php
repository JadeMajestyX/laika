<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Mascota;
use App\Models\Cita;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RecepcionistaDashboardController extends Controller
{
    public function index(Request $request, $any = null)
    {
        $usuario = auth()->user();
        return view('dashboard-recepcionista', compact('usuario'));
    }

    // Crear cliente (usuario)
    public function storeCliente(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:50',
            'password' => 'required|string|min:6',
        ]);

        try {
            $user = User::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            return response()->json(['success' => true, 'user' => $user]);
        } catch (\Exception $e) {
            Log::error('Error creando cliente: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo crear el cliente'], 500);
        }
    }

    // Crear mascota para un cliente existente
    public function storeMascota(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'nombre' => 'required|string|max:255',
            'raza' => 'nullable|string|max:255',
            'especie' => 'nullable|string|max:50',
        ]);

        try {
            $mascota = Mascota::create([
                'user_id' => $data['user_id'],
                'nombre' => $data['nombre'],
                'raza' => $data['raza'] ?? null,
                'especie' => $data['especie'] ?? null,
            ]);

            return response()->json(['success' => true, 'mascota' => $mascota]);
        } catch (\Exception $e) {
            Log::error('Error creando mascota: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo crear la mascota'], 500);
        }
    }

    // Agendar cita
    public function agendarCita(Request $request)
    {
        $data = $request->validate([
            'mascota_id' => 'required|exists:mascotas,id',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha' => 'required|date',
            'hora' => 'nullable|string|max:10',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $clinicaId = Auth::user()?->clinica_id ?? null;

            $cita = Cita::create([
                'mascota_id' => $data['mascota_id'],
                'servicio_id' => $data['servicio_id'],
                'fecha' => Carbon::parse($data['fecha'])->toDateString(),
                'hora' => $data['hora'] ?? null,
                'clinica_id' => $clinicaId,
                'status' => 'pendiente',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            // Si 'hora' no está en fillable pero fue enviada, guardarla manualmente
            if (! empty($data['hora'])) {
                $cita->hora = $data['hora'];
                $cita->save();
            }

            return response()->json(['success' => true, 'cita' => $cita]);
        } catch (\Exception $e) {
            Log::error('Error agendando cita: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo agendar la cita'], 500);
        }
    }

    // Marcar cita como completada (uso veterinario/receptionista)
    public function marcarCompletada(Request $request)
    {
        $data = $request->validate([
            'cita_id' => 'required|exists:citas,id',
        ]);

        try {
            $cita = Cita::find($data['cita_id']);
            if (! $cita) return response()->json(['error' => 'Cita no encontrada'], 404);
            $cita->status = 'completada';
            $cita->save();

            return response()->json(['success' => true, 'cita' => $cita]);
        } catch (\Exception $e) {
            Log::error('Error marcando cita completada: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo actualizar la cita'], 500);
        }
    }
}
