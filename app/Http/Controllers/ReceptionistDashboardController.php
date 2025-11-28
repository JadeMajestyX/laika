<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Mascota;
use App\Models\Cita;
use App\Models\Servicio;
use Carbon\Carbon;

class ReceptionistDashboardController extends Controller
{
    public function index(Request $request)
    {
        $usuario = auth()->user();
        return view('dashboard-receptionist', compact('usuario'));
    }

    // Crear cuenta para cliente
    public function crearCliente(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'nullable|string|min:6',
                'telefono' => 'nullable|string|max:50',
            ]);

            $clinicaId = Auth::user()?->clinica_id;
            $password = $validated['password'] ?? str()->random(8);

            $user = User::create([
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'telefono' => $validated['telefono'] ?? null,
                'clinica_id' => $clinicaId,
                'rol' => 'C', // Cliente
            ]);

            return response()->json(['success' => true, 'user' => $user]);
        } catch (\Exception $e) {
            Log::error('Recepcionista crearCliente error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'No se pudo crear el cliente'], 500);
        }
    }

    // Buscar clientes y mascotas por nombre/email
    public function buscarClienteMascota(Request $request)
    {
        $term = trim($request->query('q', ''));
        $clinicaId = Auth::user()?->clinica_id;

        $users = User::where('rol', 'C')
            ->where('clinica_id', $clinicaId)
            ->where(function($q) use ($term){
                $q->where('nombre', 'like', "%$term%")
                  ->orWhere('email', 'like', "%$term%");
            })
            ->limit(10)
            ->get(['id','nombre','email','telefono']);

        $mascotas = Mascota::where('clinica_id', $clinicaId)
            ->where(function($q) use ($term){
                $q->where('nombre', 'like', "%$term%")
                  ->orWhere('raza', 'like', "%$term%");
            })
            ->limit(10)
            ->get(['id','nombre','raza','user_id']);

        return response()->json(['success' => true, 'users' => $users, 'mascotas' => $mascotas]);
    }

    // Agendar cita para cliente/mascota
    public function agendarCita(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'mascota_id' => 'required|exists:mascotas,id',
                'servicio_id' => 'required|exists:servicios,id',
                'fecha' => 'required|date',
                'hora' => 'nullable|string',
                'nota' => 'nullable|string',
            ]);

            $clinicaId = Auth::user()?->clinica_id;

            $cita = Cita::create([
                'user_id' => $validated['user_id'],
                'mascota_id' => $validated['mascota_id'],
                'servicio_id' => $validated['servicio_id'],
                'clinica_id' => $clinicaId,
                'fecha' => Carbon::parse($validated['fecha']),
                'hora' => $validated['hora'] ?? null,
                'status' => 'pendiente',
                'nota' => $validated['nota'] ?? null,
                'creador_id' => Auth::id(),
            ]);

            return response()->json(['success' => true, 'cita' => $cita]);
        } catch (\Exception $e) {
            Log::error('Recepcionista agendarCita error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'No se pudo agendar la cita'], 500);
        }
    }

    // Marcar cita como completada (veterinarios)
    public function completarCita(Request $request, Cita $cita)
    {
        try {
            $cita->status = 'completada';
            $cita->save();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Recepcionista completarCita error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'No se pudo completar la cita'], 500);
        }
    }
}
