<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GroomerDashboardController extends Controller
{
    public function index(Request $request, $any = null)
    {
        $usuario = auth()->user();
        return view('dashboard-groomer', compact('usuario'));
    }

    public function getDashboardData()
    {
        // Datos de ejemplo iniciales; reemplazar con consultas reales a Citas del groomer.
        $data = [
            'citasHoy' => 6,
            'citasCompletadas' => 3,
            'serviciosRealizados' => 4,
            'mascotasAtendidas' => 5,

            'citasPorDia' => [
                ['dia' => 'Monday', 'total' => 8],
                ['dia' => 'Tuesday', 'total' => 10],
                ['dia' => 'Wednesday', 'total' => 7],
                ['dia' => 'Thursday', 'total' => 9],
                ['dia' => 'Friday', 'total' => 12],
                ['dia' => 'Saturday', 'total' => 5],
                ['dia' => 'Sunday', 'total' => 1],
            ],

            'actividades' => [
            ],

            'citas' => [
                [
                    'hora' => '10:00',
                    'mascota' => ['nombre' => 'Kira', 'raza' => 'Poodle'],
                    'propietario' => 'Luis Gómez',
                    'servicio' => ['nombre' => 'Baño y Cepillado'],
                    'status' => 'pendiente'
                ],
                [
                    'hora' => '12:00',
                    'mascota' => ['nombre' => 'Simba', 'raza' => 'Golden Retriever'],
                    'propietario' => 'Ana Ruiz',
                    'servicio' => ['nombre' => 'Corte de Uñas'],
                    'status' => 'confirmada'
                ]
            ],

            'comparacionporcentaje' => [
                'citasHoy' => 10,
                'citasCompletadas' => 5,
                'serviciosRealizados' => 7,
                'mascotasAtendidas' => 9,
            ]
        ];

        return response()->json($data);
    }
}
