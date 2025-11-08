<?php
// app/Http/Controllers/VetDashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Actividad;
use Carbon\Carbon;

class VetDashboardController extends Controller
{
    public function index(Request $request, $any = null)
    {
        $usuario = auth()->user();
        return view('dashboard-vet', compact('usuario'));
    }

    public function getDashboardData()
    {
        // Datos de prueba
        $data = [
            'citasHoy' => 8,
            'citasCompletadas' => 5,
            'consultasRealizadas' => 12,
            'mascotasAtendidas' => 7,
            
            'citasPorDia' => [
                ['dia' => 'Monday', 'total' => 10],
                ['dia' => 'Tuesday', 'total' => 15],
                ['dia' => 'Wednesday', 'total' => 8],
                ['dia' => 'Thursday', 'total' => 12],
                ['dia' => 'Friday', 'total' => 18],
                ['dia' => 'Saturday', 'total' => 6],
                ['dia' => 'Sunday', 'total' => 2],
            ],
            
            'actividades' => [
                
            ],
            
            'citas' => [
                [
                    'hora' => '09:00',
                    'mascota' => ['nombre' => 'Max', 'raza' => 'Labrador'],
                    'propietario' => 'Carlos López',
                    'clinica' => ['nombre' => 'Clínica Central'],
                    'servicio' => ['nombre' => 'Consulta General'],
                    'status' => 'pendiente'
                ],
                [
                    'hora' => '11:00', 
                    'mascota' => ['nombre' => 'Luna', 'raza' => 'Siamés'],
                    'propietario' => 'María Rodríguez',
                    'clinica' => ['nombre' => 'Clínica Central'],
                    'servicio' => ['nombre' => 'Vacunación'],
                    'status' => 'completada'
                ],
                [
                    'hora' => '14:00',
                    'mascota' => ['nombre' => 'Rocky', 'raza' => 'Bulldog'],
                    'propietario' => 'Juan Pérez', 
                    'clinica' => ['nombre' => 'Clínica Central'],
                    'servicio' => ['nombre' => 'Desparasitación'],
                    'status' => 'confirmada'
                ]
            ],
            
            'comparacionporcentaje' => [
                'citasHoy' => 15,
                'citasCompletadas' => 8,
                'consultasRealizadas' => 12,
                'mascotasAtendidas' => 10,
            ]
        ];

        return response()->json($data);
    }
}