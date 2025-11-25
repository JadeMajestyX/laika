<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Google_Client;
use App\Support\ActivityLogger;
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

            // Buscar usuario existente
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Crear usuario solo si no existe
                $user = User::create([
                    'email' => $email,
                    'nombre' => $nombre,
                    'apellido_paterno' => $apellido,
                    'imagen_perfil' => $imagen,
                    'rol' => 'U',
                    'genero' => 'O',
                    'fecha_nacimiento' => now(),
                    'telefono' => null,
                    'password' => Hash::make(str()->random(16)),
                ]);

                // Enviar correo de bienvenida
                try {
                    $user->notify(new WelcomeNotification());
                } catch (\Throwable $e) {
                    // Evitar que un fallo de correo bloquee el login
                }
                // Log de actividad: registro de usuario con Google (solo cuando se crea)
                ActivityLogger::log($request, 'Registro de usuario (Google)', 'User', $user->id, [
                    'email' => $user->email,
                    'nombre' => $user->nombre,
                ], $user->id);
            } else {
                // Log de actividad: inicio de sesión con Google (usuario existente)
                ActivityLogger::log($request, 'Inicio de sesión (Google)', 'User', $user->id, [
                    'email' => $user->email,
                ], $user->id);
            }
            // Generar token
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
