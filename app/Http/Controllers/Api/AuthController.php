<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Google_Client;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginWithGoogle(Request $request)
    {
        $idToken = $request->input('token');

        $client = new Google_Client(['client_id' => '486812368955-g2lqfgf19duj42ch3kmh57il53videto.apps.googleusercontent.com']);
        $payload = $client->verifyIdToken($idToken);

        if ($payload) {
            $email = $payload['email'];
            $nombre = $payload['given_name'] ?? '';
            $apellido = $payload['family_name'] ?? '';
            $imagen = $payload['picture'] ?? null;

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'nombre' => $nombre,
                    'apellido_paterno' => $apellido,
                    'imagen_perfil' => $imagen,
                    'rol' => 'U',
                    'genero' => 'O',
                    'fecha_nacimiento' => now(),
                    'telefono' => null,
                    'password' => Hash::make(str()->random(16)),
                ]
            );

            // Si usas Laravel Sanctum
            $token = $user->createToken('mobile')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'token' => $token,
            ]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Token inválido'], 401);
        }
    }
}
