<?php

use App\Http\Controllers\Api\AuthController;
use App\Models\Cita;
use App\Models\CodigoDispensador;
use App\Models\Dispensador;
use App\Models\Mascota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Models\Medicion;
use App\Models\DeviceToken;
use App\Services\FcmV1Client;
use App\Models\Status;
use App\Models\Horario;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Notifications\ResetPasswordCodeNotification;
use App\Notifications\WelcomeNotification;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\NotificationsController;
use Carbon\Carbon;

Route::post('/login', function(Request $request){
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string'
    ]);

    $user = User::where('email', $request->email)->first();

    if(!$user || !Hash::check($request->password, $user->password)){
        return response()->json([
            'message' => 'Credenciales incorrectas'
        ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
       'access_token' => $token,
       'token_type' => 'Bearer',
       'user' => $user
    ]);
});


Route::post('/register', function(Request $request){
    $request->validate([
        'nombre' => 'required|string|max:100',
        'apellido_paterno' => 'required|string|max:100',
        'apellido_materno' => 'nullable|string|max:100',
        'fecha_nacimiento' => 'required|date',
        'genero' => 'required|in:M,F,O',
        'email' => 'required|email|max:150|unique:users,email',
        'telefono' => 'required|string|max:15|unique:users,telefono',
        'imagen_perfil' => 'nullable|string|max:100',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'nombre' => $request->nombre,
        'apellido_paterno' => $request->apellido_paterno,
        'apellido_materno' => $request->apellido_materno,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'genero' => $request->genero,
        'email' => $request->email,
        'telefono' => $request->telefono,
        'imagen_perfil' => $request->imagen_perfil,
        'password' => Hash::make($request->password)
    ]);

    // Log de actividad: registro de usuario
    ActivityLogger::log($request, 'Registro de usuario', 'User', $user->id, [
        'email' => $user->email,
        'nombre' => $user->nombre,
    ], $user->id);

    // Enviar correo de bienvenida
    try {
        $user->notify(new WelcomeNotification());
    } catch (\Throwable $e) {
        // Evitar que un fallo de correo rompa el registro
        Log::warning('Fallo al enviar correo de bienvenida: ' . $e->getMessage());
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
       'access_token' => $token,
       'token_type' => 'Bearer',
       'user' => $user
    ]);
})->middleware('throttle:3,1');

Route::post('/mediciones', function(Request $request){
    $request->validate([
        'codigo' => 'required|string|exists:codigo_dispensadors,codigo',
        'nivel_comida' => 'required|integer|min:0|max:100',
        'peso_comida' => 'required|numeric|min:0|max:1000',
    ]);

    // Crear la medición
    $medicion = Medicion::create([
        'dispensador_id' => CodigoDispensador::where('codigo', $request->codigo)->first()->id,
        'nivel_comida' => $request->nivel_comida,
        'peso_comida' => $request->peso_comida,
    ]);

    return response()->json([
        'message' => 'Medición creada exitosamente',
        'data' => $medicion
    ]);
});

Route::post('/register-mascota', function(Request $request) {
    // 🔹 Validar los datos recibidos
    $request->validate([
        'nombre' => 'required|string|max:100',
        'especie' => 'required|string|max:100',
        'raza' => 'nullable|string|max:100',
        'fecha_nacimiento' => 'required|date',
        'sexo' => 'required|in:M,F,O',
        'peso' => 'required|numeric|min:0|max:200',
        'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // imagen opcional
        'user_id' => 'required|exists:users,id'
    ]);

    // 🔹 Procesar la imagen si viene en el request
    $imagePath = null; // ruta relativa (si se guarda)
    $imageName = null; // nombre del archivo que guardaremos en BD
    if ($request->hasFile('imagen')) {
        $extension = $request->file('imagen')->getClientOriginalExtension();
        $imageName = time() . '_' . uniqid() . '.' . $extension; // nombre único
        $destinationPath = public_path('uploads/mascotas');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        // Mover el archivo directamente a public/uploads/mascotas
        $request->file('imagen')->move($destinationPath, $imageName);
        $imagePath = 'uploads/mascotas/' . $imageName;
    }

    // 🔹 Crear registro en base de datos
    $mascota = Mascota::create([
        'nombre' => $request->nombre,
        'especie' => $request->especie,
        'raza' => $request->raza,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'sexo' => $request->sexo,
        'peso' => $request->peso,
        // Guardamos SOLO el nombre del archivo en la BD, como solicitó el cliente
        'imagen' => $imageName,
        'user_id' => $request->user_id
    ]);

    // Log de actividad: registro de mascota
    ActivityLogger::log($request, 'Registro de mascota', 'Mascota', $mascota->id, [
        'nombre' => $mascota->nombre,
        'user_id' => $mascota->user_id,
    ], $request->user_id);

    // 🔹 Responder con JSON
    return response()->json([
        'message' => 'Mascota registrada exitosamente',
        'data' => [
            'id' => $mascota->id,
            'nombre' => $mascota->nombre,
            'especie' => $mascota->especie,
            'raza' => $mascota->raza,
            'fecha_nacimiento' => $mascota->fecha_nacimiento,
            'sexo' => $mascota->sexo,
            'peso' => $mascota->peso,
            // Construimos la URL pública desde public/uploads/mascotas
            'imagen_url' => $imagePath ? asset($imagePath) : null,
            'user_id' => $mascota->user_id,
        ]
    ]);
});


Route::middleware('auth:sanctum')->get('/mis-mascotas', function(Request $request) {
    $mascotas = Mascota::where('user_id', $request->user()->id)
        ->get()
        ->map(function ($mascota) {
            return [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre,
                'especie' => $mascota->especie,
                'raza' => $mascota->raza,
                'fecha_nacimiento' => $mascota->fecha_nacimiento,
                'edad' => \Carbon\Carbon::parse($mascota->fecha_nacimiento)->age . ' años',
                'peso' => $mascota->peso,
                // nombre del archivo de la imagen (puede ser null)
                'imagen' => $mascota->imagen,
                // URL pública construida a partir de public/uploads/mascotas
                'imagen_url' => $mascota->imagen ? asset('uploads/mascotas/' . $mascota->imagen) : null,
            ];
        });

    return response()->json([
        'success' => true,
        'mascotas' => $mascotas
    ]);
});


//mis dispensadores con el usuario autenticado con token
Route::middleware('auth:sanctum')->get('/mis-dispensadores', function(Request $request) {
    $dispensadores = Dispensador::where('usuario_id', $request->user()->id)->with('codigoDispensador')->get()
    ->map(function ($dispensador) {
        return [
            'id' => $dispensador->id,
            'codigo' => $dispensador->codigoDispensador->codigo,
            'nombre' => $dispensador->nombre,
            'mascota_nombre' => $dispensador->mascota->nombre ?? null,
        ];
    });

    return response()->json([
        'success' => true,
        'dispensadores' => $dispensadores
    ]);
});

    // Desvincular un dispensador del usuario (owner o admin)
    Route::middleware('auth:sanctum')->post('/dispensadores/{id}/desvincular', function(Request $request, $id) {
        $user = $request->user();
        $disp = Dispensador::with('codigoDispensador')->find($id);
        if (!$disp) {
            return response()->json(['success' => false, 'message' => 'Dispensador no encontrado'], 404);
        }

        // Permite si es dueño o si es admin (rol A)
        if ($user->id !== $disp->usuario_id && ($user->rol ?? null) !== 'A') {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $codigo = $disp->codigoDispensador->codigo ?? null;
        $oldOwner = $disp->usuario_id;
        $disp->usuario_id = null;
        try {
            $disp->save();
            ActivityLogger::log($request, 'Desvincular dispensador', 'Dispensador', $disp->id, [
                'codigo' => $codigo,
                'old_owner' => $oldOwner,
            ], $user->id);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error al desvincular dispensador'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Dispensador desvinculado correctamente']);
    });

// Device tokens: registrar/actualizar token del dispositivo del usuario
Route::middleware('auth:sanctum')->post('/device-tokens', [DeviceTokenController::class, 'store']);
Route::middleware('auth:sanctum')->delete('/device-tokens/{token}', [DeviceTokenController::class, 'destroy']);

// Internal push endpoint: enviar notificación (user_id o topic). Protegido por HEADER X-INTERNAL-KEY (env INTERNAL_PUSH_KEY) o puede quedar abierto si se configura.
Route::post('/notifications/push', [NotificationsController::class, 'push']);

Route::get('/estado-dispensador', function (Request $request) {
    $codigo = $request->query('codigo');

    if (!$codigo) {
        return response()->json(['success' => false, 'message' => 'Código no proporcionado'], 400);
    }

    // Buscar el dispensador por su código
    $dispensador = CodigoDispensador::where('codigo', $codigo)->first();

    if (!$dispensador) {
        return response()->json(['success' => false, 'message' => 'Dispensador no encontrado'], 404);
    }

    // Buscar o crear el estado
    $statuss = Status::firstOrCreate(
        ['dispensador_id' => $dispensador->id],
        ['status' => false]
    );

    // Guardamos el estado actual antes de modificarlo
    $estadoActual = $statuss->status;

    // Si el estado era true, cambiarlo a false y guardar en BD
    if ($estadoActual === true) {

        //actualizamos el estado a false
        $statuss->status = false;
        $statuss->save();
    }

    return response()->json([
        'success' => true,
        'estado' => $estadoActual ? 1 : 0,
        'message' => $estadoActual ? 'Estado cambiado a false' : 'Estado ya era false'
    ]);
});


// calibrar sensor (misma lógica que estado-dispensador pero usando el campo 'calibrar')
Route::get('/calibrar', function (Request $request) {
    // Respuesta fija: no consulta BD, siempre retorna 0
    return response()->json([
        'success' => true,
        'calibrar' => 0,
        'message' => 'Calibrar está en false'
    ]);
});

// activar calibración (misma lógica que activar-dispensador pero usando el campo 'calibrar')
Route::get('/activar-calibrar', function (Request $request) {
    $codigo = $request->query('codigo');
    if (!$codigo) {
        return response()->json(['success' => false, 'message' => 'Código no proporcionado'], 400);
    }

    // Buscar el dispensador por su código
    $dispensador = CodigoDispensador::where('codigo', $codigo)->first();
    if (!$dispensador) {
        return response()->json(['success' => false, 'message' => 'Dispensador no encontrado'], 404);
    }

    // Buscar o crear el estado (calibrar=false por defecto)
    $statuss = Status::firstOrCreate(
        ['dispensador_id' => $dispensador->id],
        ['status' => false, 'calibrar' => false]
    );

    // Guardamos el estado actual de calibrar antes de modificarlo
    $calibrarActual = (bool) $statuss->calibrar;

    // Si estaba en false, cambiarlo a true y guardar en BD
    if ($calibrarActual === false) {
        $statuss->calibrar = true;
        $statuss->save();
    }

    return response()->json([
        'success' => true,
        'calibrar' => $calibrarActual ? 1 : 0,
        'message' => $calibrarActual ? 'Calibrar cambiado a true' : 'Calibrar ya era true'
    ]);
});


// Activar dispensador con objetivo de peso (cantidad)
// Lógica: recibe 'codigo' y 'cantidad' (peso objetivo). Consulta últimas mediciones;
// si el peso_actual >= cantidad, pone status=0 (detener). Si aún no alcanza, pone status=1 (seguir dispensando).
Route::match(['get','post'], '/activar-dispensador', function (Request $request) {
    $codigo = $request->input('codigo') ?? $request->query('codigo');
    $cantidad = $request->input('cantidad') ?? $request->query('cantidad');

    if (!$codigo) {
        return response()->json(['success' => false, 'message' => 'Código no proporcionado'], 400);
    }

    // Validar cantidad si llega
    if ($cantidad !== null) {
        if (!is_numeric($cantidad) || (float)$cantidad <= 0) {
            return response()->json(['success' => false, 'message' => 'Cantidad inválida'], 422);
        }
        $cantidad = (float)$cantidad;
    }

    // Buscar el dispensador por su código
    $codigoModel = CodigoDispensador::where('codigo', $codigo)->first();
    if (!$codigoModel) {
        return response()->json(['success' => false, 'message' => 'Dispensador no encontrado'], 404);
    }

    // Buscar o crear el estado de control
    $statuss = Status::firstOrCreate(
        ['dispensador_id' => $codigoModel->id],
        ['status' => false, 'calibrar' => false]
    );

    // Obtener última medición registrada para este dispensador (en esquema actual: 'dispensador_id' guarda id del código)
    $ultimaMedicion = Medicion::where('dispensador_id', $codigoModel->id)
        ->orderByDesc('id')
        ->first();

    $pesoActual = $ultimaMedicion?->peso_comida ?? 0.0;

    // Si no se proporcionó cantidad, solo activar (status=1) para iniciar dispensado
    if ($cantidad === null) {
        $statuss->status = true; // 1 = dispensando
        $statuss->save();
        return response()->json([
            'success' => true,
            'estado' => 1,
            'message' => 'Dispensador activado (sin objetivo).',
            'peso_actual' => (float)$pesoActual,
        ]);
    }

    // Con objetivo: el objetivo real es peso_actual + cantidad solicitada
    $objetivo = $pesoActual + $cantidad;

    // Fase de observación de 20s: al recibir una cantidad (p.ej. 200g),
    // 1) activar dispensado y guardar peso base y timestamp
    // 2) tras 20s, si el peso aumentó -> continuar; si no -> detener y notificar.
    $obsKey = 'disp_obs_' . $codigoModel->id;
    $epsilon20 = 1.0; // umbral mínimo de incremento (1g)
    $ahora = Carbon::now();
    $obs = Cache::get($obsKey);

    if (!$obs) {
        // Primer paso: iniciar dispensado y registrar observación de 20s
        Cache::put($obsKey, [
            'peso_base' => (float) $pesoActual,
            'objetivo' => (float) $objetivo,
            'inicio' => $ahora->toDateTimeString(),
        ], now()->addMinutes(10));

        $statuss->status = true; // iniciar dispensado
        $statuss->save();

        return response()->json([
            'success' => true,
            'estado' => 1,
            'message' => 'Inicio de dispensado; observando 20s para verificar incremento.',
            'peso_actual' => (float)$pesoActual,
            'objetivo' => (float)$objetivo,
            'observacion_20s' => true,
            'esperar_segundos' => 20,
        ]);
    } else {
        // Observación en curso: verificar si ya pasaron 20s
        try { $inicioObs = Carbon::parse($obs['inicio']); } catch (\Throwable $e) { $inicioObs = $ahora->copy()->subSeconds(21); }
        $transcurridos = $inicioObs->diffInSeconds($ahora);

        if ($transcurridos < 20) {
            // Aún en ventana de 20s: mantener dispensado
            $statuss->status = true;
            $statuss->save();

            return response()->json([
                'success' => true,
                'estado' => 1,
                'message' => 'Esperando 20s para evaluar incremento.',
                'segundos_restantes' => 20 - $transcurridos,
                'peso_actual' => (float)$pesoActual,
                'objetivo' => (float)$obs['objetivo'],
            ]);
        }

        // Pasados 20s: comprobar incremento respecto al peso base
        $pesoBase = (float)($obs['peso_base'] ?? 0.0);
        $objetivoObs = (float)($obs['objetivo'] ?? $objetivo);

        if ((float)$pesoActual > ($pesoBase + $epsilon20)) {
            // Hubo incremento: evaluar objetivo
            if ((float)$pesoActual >= $objetivoObs) {
                // Objetivo alcanzado: detener y limpiar observación
                $statuss->status = false;
                $statuss->save();
                Cache::forget($obsKey);

                return response()->json([
                    'success' => true,
                    'estado' => 0,
                    'message' => 'Objetivo alcanzado tras verificación de 20s. Dispensador detenido.',
                    'peso_actual' => (float)$pesoActual,
                    'objetivo' => (float)$objetivoObs,
                ]);
            } else {
                // Aún no alcanza: continuar dispensando y re-armar otra observación de 20s
                Cache::put($obsKey, [
                    'peso_base' => (float) $pesoActual,
                    'objetivo' => (float) $objetivoObs,
                    'inicio' => $ahora->toDateTimeString(),
                ], now()->addMinutes(10));

                $statuss->status = true;
                $statuss->save();

                return response()->json([
                    'success' => true,
                    'estado' => 1,
                    'message' => 'Incremento detectado; continuando dispensado y observando otros 20s.',
                    'peso_actual' => (float)$pesoActual,
                    'objetivo' => (float)$objetivoObs,
                    'restante' => max(0.0, $objetivoObs - (float)$pesoActual),
                    'observacion_20s' => true,
                ]);
            }
        } else {
            // No hubo incremento tras 20s: detener, notificar y limpiar observación
            $statuss->status = false;
            $statuss->save();
            Cache::forget($obsKey);

            try {
                $dispensadorReal = Dispensador::where('codigo_dispensador_id', $codigoModel->id)->first();
                $userId = $dispensadorReal?->usuario_id;
                if ($userId && config('fcm.use_v1')) {
                    $client = new FcmV1Client();
                    $title = 'Problema con dispensador';
                    $body = 'No se detectó incremento de peso después de 20s. Revisa el dispensador.';
                    $dataPayload = [
                        'tipo' => 'dispensador_problema',
                        'codigo' => $codigo,
                    ];
                    $client->sendToUser($userId, $title, $body, $dataPayload);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Error notificando problema de dispensador (20s): ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'estado' => 0,
                'message' => 'Sin incremento tras 20s. Dispensador detenido y problema notificado.',
                'peso_actual' => (float)$pesoActual,
                'objetivo' => (float)$objetivo,
            ]);
        }
    }

    // (Fallback previo) Verificar si en las últimas 3 lecturas no ha cambiado el peso
    // y que dicha condición se mantenga durante una ventana de 30s.
    $ultimas = Medicion::where('dispensador_id', $codigoModel->id)
        ->orderByDesc('id')
        ->take(3)
        ->get();

    $sinCambio = false;
    $ventanaOk = false; // true si el rango temporal entre la medición más reciente y la más antigua >= 30s
    if ($ultimas->count() >= 3) {
        // Considerar un umbral pequeño (1g) para variaciones
        $epsilon = 1.0;
        $pesos = $ultimas->pluck('peso_comida')->all();
        // Comparar todas entre sí respecto al primero
        $base = (float) $pesos[0];
        $sinCambio = true;
        foreach ($pesos as $p) {
            if (abs(((float)$p) - $base) > $epsilon) {
                $sinCambio = false;
                break;
            }
        }

        // Evaluar ventana de 30s usando created_at de las mediciones
        try {
            $masReciente = \Carbon\Carbon::parse($ultimas[0]->created_at);
            $masAntigua = \Carbon\Carbon::parse($ultimas[$ultimas->count()-1]->created_at);
            $ventanaOk = $masReciente->diffInSeconds($masAntigua) >= 30;
        } catch (\Throwable $e) {
            $ventanaOk = false;
        }
    }

    if ($sinCambio && $ventanaOk) {
        // Detener dispensado
        $statuss->status = false; // 0 = detener
        $statuss->save();

        // Notificar al dueño del dispensador si existe
        try {
            $dispensadorReal = Dispensador::where('codigo_dispensador_id', $codigoModel->id)->first();
            $userId = $dispensadorReal?->usuario_id;
            if ($userId && config('fcm.use_v1')) {
                $client = new FcmV1Client();
                $title = 'Problema con dispensador';
                $body = 'No se detecta cambio de peso tras 3 intentos. Revisa el mecanismo.';
                $dataPayload = [
                    'tipo' => 'dispensador_problema',
                    'codigo' => $codigo,
                ];
                $client->sendToUser($userId, $title, $body, $dataPayload);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error notificando problema de dispensador: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'estado' => 0,
            'message' => 'Sin cambio en 3 lecturas. Dispensador detenido y problema notificado.',
            'peso_actual' => (float)$pesoActual,
            'objetivo' => (float)$objetivo,
        ]);
    }

    // Si no hubo cambio pero la ventana de 30s aún no se cumple, seguimos dispensando sin notificar
    if ($sinCambio && !$ventanaOk) {
        $statuss->status = true; // 1 = dispensando
        $statuss->save();
        return response()->json([
            'success' => true,
            'estado' => 1,
            'message' => 'Evaluando: aún no se cumplen 30s de observación.',
            'peso_actual' => (float)$pesoActual,
            'objetivo' => (float)$objetivo,
        ]);
    }

    // Decidir si continuar dispensando o detener en función del objetivo
    if ($pesoActual >= $objetivo) {
        // Objetivo alcanzado: detener
        $statuss->status = false; // 0 = detener
        $statuss->save();
        return response()->json([
            'success' => true,
            'estado' => 0,
            'message' => 'Objetivo alcanzado. Dispensador detenido.',
            'peso_actual' => (float)$pesoActual,
            'objetivo' => (float)$objetivo,
        ]);
    } else {
        // Aún no alcanza: activar
        $statuss->status = true; // 1 = dispensando
        $statuss->save();
        return response()->json([
            'success' => true,
            'estado' => 1,
            'message' => 'Dispensando hasta alcanzar el objetivo.',
            'peso_actual' => (float)$pesoActual,
            'objetivo' => (float)$objetivo,
        ]);
    }
});



//actualizar mascota (PUT multipart puede no detectar archivos en algunos entornos; se agrega también POST con _method spoofing)
Route::middleware('auth:sanctum')->put('/actualizar-mascota/{id}', function(Request $request, $id){
    $mascota = Mascota::find($id);

    if (!$mascota) {
        return response()->json(['message' => 'Mascota no encontrada'], 404);
    }

    $request->validate([
        'nombre' => 'nullable|string|max:100',
        'especie' => 'nullable|string|max:100',
        'raza' => 'nullable|string|max:100',
        'fecha_nacimiento' => 'nullable|date',
        'sexo' => 'nullable|string|in:M,F,O',
        'peso' => 'nullable|numeric|min:0|max:200',
        // ahora permitimos subir imagen como en register-mascota
        'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    // Datos a actualizar (sin imagen por ahora)
    $updateData = $request->only([
        'nombre',
        'especie',
        'raza',
        'fecha_nacimiento',
        'sexo',
        'peso',
    ]);

    // Procesar imagen si viene en el request (multipart/form-data)
    if ($request->hasFile('imagen')) {
        $extension = $request->file('imagen')->getClientOriginalExtension();
        $imageName = time() . '_' . uniqid() . '.' . $extension; // nombre único
        $destinationPath = public_path('uploads/mascotas');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        $request->file('imagen')->move($destinationPath, $imageName);
        // Guardamos solo el nombre del archivo en BD (consistente con register-mascota)
        $updateData['imagen'] = $imageName;
    }

    $mascota->update($updateData);

    return response()->json([
        'message' => 'Mascota actualizada exitosamente',
        'data' => array_merge($mascota->toArray(), [
            'imagen_url' => $mascota->imagen ? asset('uploads/mascotas/' . $mascota->imagen) : null,
        ])
    ]);
});

// Alternativa para clientes que envían multipart/form-data solo con POST.
Route::middleware('auth:sanctum')->post('/actualizar-mascota/{id}', function(Request $request, $id){
    // Permitir spoofing del método si viene _method=PUT, pero tratamos igual.
    $mascota = Mascota::find($id);
    if (!$mascota) {
        return response()->json(['message' => 'Mascota no encontrada'], 404);
    }

    $request->validate([
        'nombre' => 'nullable|string|max:100',
        'especie' => 'nullable|string|max:100',
        'raza' => 'nullable|string|max:100',
        'fecha_nacimiento' => 'nullable|date',
        'sexo' => 'nullable|string|in:M,F,O',
        'peso' => 'nullable|numeric|min:0|max:200',
        'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $updateData = $request->only([
        'nombre', 'especie', 'raza', 'fecha_nacimiento', 'sexo', 'peso'
    ]);

    if ($request->hasFile('imagen')) {
        // Eliminar imagen anterior si existe (limpieza opcional)
        if ($mascota->imagen) {
            $oldPath = public_path('uploads/mascotas/' . $mascota->imagen);
            if (is_file($oldPath)) { @unlink($oldPath); }
        }
        $extension = $request->file('imagen')->getClientOriginalExtension();
        $imageName = time() . '_' . uniqid() . '.' . $extension;
        $destinationPath = public_path('uploads/mascotas');
        if (!file_exists($destinationPath)) { mkdir($destinationPath, 0755, true); }
        $request->file('imagen')->move($destinationPath, $imageName);
        $updateData['imagen'] = $imageName;
    }

    $mascota->update($updateData);

    return response()->json([
        'message' => 'Mascota actualizada exitosamente',
        'data' => array_merge($mascota->toArray(), [
            'imagen_url' => $mascota->imagen ? asset('uploads/mascotas/' . $mascota->imagen) : null,
        ])
    ]);
});


//obtener las ultimas 10 mediciones de un dispensador
Route::middleware('auth:sanctum')->get('/mediciones-dispensador/{id}', function(Request $request, $id){
    $dispensador = Dispensador::find($id);
    if (!$dispensador) {
        return response()->json(['message' => 'Dispensador no encontrado'], 404);
    }

    $mediciones = Medicion::where('dispensador_id', $dispensador->id)
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

    return response()->json([
        'success' => true,
        'mediciones' => $mediciones
    ]);
});


//mediciones por rango de fechas
Route::middleware('auth:sanctum')->get('/mediciones-rango/{id}', function(Request $request, $id){
    $dispensador = Dispensador::find($id);
    if (!$dispensador) {
        return response()->json(['message' => 'Dispensador no encontrado'], 404);
    }
    $startDate = $request->query('start_date');
    $endDate = $request->query('end_date');
    $mediciones = Medicion::where('dispensador_id', $dispensador->id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->orderBy('created_at', 'desc')
        ->get();
    return response()->json([
        'success' => true,
        'mediciones' => $mediciones
    ]);
});


//obtener citas de una mascota
Route::middleware('auth:sanctum')->get('/citas-mascota/{id}', function(Request $request, $id){
    $mascota = Mascota::find($id);
    if (!$mascota) {
        return response()->json(['message' => 'Mascota no encontrada'], 404);
    }

    $citas = Cita::where('mascota_id', $mascota->id)->get();

    return response()->json([
        'success' => true,
        'citas' => $citas
    ]);
});


// Obtener las citas de las mascotas del usuario autenticado
Route::middleware('auth:sanctum')->get('/mis-citas', function(Request $request) {

    // Obtener los IDs de las mascotas del usuario
    $mascotasIds = Mascota::where('user_id', $request->user()->id)->pluck('id');

    // Traer las citas de esas mascotas con las relaciones necesarias
    $citas = Cita::with(['mascota', 'servicio'])
                 ->whereIn('mascota_id', $mascotasIds)
                 ->get();

    // Formatear la respuesta
    $resultado = $citas->map(function($cita) {
        return [
            'id' => $cita->id,
            'mascota' => $cita->mascota ? $cita->mascota->nombre : null,
            'motivo' => $cita->servicio ? $cita->servicio->nombre : null,
            'fecha' => \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y H:i'),
        ];
    });

    return response()->json([
        'success' => true,
        'citas' => $resultado
    ]);
});


//agendar cita
Route::middleware('auth:sanctum')->post('/agendar-cita', function(Request $request){
    $request->validate([
        'clinica_id' => 'required|exists:clinicas,id',
        'servicio_id' => 'required|exists:servicios,id',
        'mascota_id' => 'required|exists:mascotas,id',
        'fecha' => 'required|date|after:today',
        'notas' => 'nullable|string|max:500',
    ]);

    // Normalizar fecha a inicio de hora y prevenir doble reserva (misma clínica, mismo día y hora)
    try {
        $slotInicio = \Carbon\Carbon::parse($request->fecha, config('app.timezone'))
            ->minute(0)->second(0);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Formato de fecha inválido'
        ], 422);
    }

    // Ocupada si hay cualquier cita dentro del rango de esa hora [inicio, fin)
    $slotFin = $slotInicio->copy()->addHour();
    $ocupada = Cita::where('clinica_id', $request->clinica_id)
        ->where('fecha', '>=', $slotInicio->toDateTimeString())
        ->where('fecha', '<', $slotFin->toDateTimeString())
        ->exists();

    if ($ocupada) {
        return response()->json([
            'success' => false,
            'message' => 'Horario ocupado para esa fecha y hora',
            'hora' => $slotInicio->format('H:00'),
            'fecha' => $slotInicio->toDateString(),
        ], 422);
    }

    // Crear la cita con fecha normalizada al inicio de la hora
    $cita = Cita::create([
        'clinica_id' => $request->clinica_id,
        'servicio_id' => $request->servicio_id,
        'creada_por' => $request->user()->id,
        'mascota_id' => $request->mascota_id,
        'fecha' => $slotInicio->toDateTimeString(),
        'notas' => $request->notas,
    ]);

    // Log de actividad: agendar cita
    ActivityLogger::log($request, 'Agendar cita', 'Cita', $cita->id, [
        'clinica_id' => $cita->clinica_id,
        'servicio_id' => $cita->servicio_id,
        'mascota_id' => $cita->mascota_id,
        'fecha' => $cita->fecha,
    ], $request->user()->id);

    return response()->json([
        'success' => true,
        'cita' => $cita
    ]);
});

// Detalle de una cita (incluye mascota y receta)
Route::middleware('auth:sanctum')->get('/citas/{id}', function(Request $request, $id) {
    $cita = \App\Models\Cita::with(['mascota','servicio','receta.items'])->find($id);
    if (!$cita) {
        return response()->json(['success'=>false,'message'=>'Cita no encontrada'],404);
    }
    $mascota = $cita->mascota;
    $mascotaData = null;
    if ($mascota) {
        $mascotaData = [
            'id' => $mascota->id,
            'nombre' => $mascota->nombre,
            'especie' => $mascota->especie,
            'raza' => $mascota->raza,
            'sexo' => $mascota->sexo,
            'peso' => $mascota->peso,
            'imagen_url' => $mascota->imagen ? asset('uploads/mascotas/' . $mascota->imagen) : null,
        ];
    }
    return response()->json([
        'success'=>true,
        'cita'=>$cita,
        'mascota'=>$mascotaData,
        'receta'=>$cita->receta ? [
            'id'=>$cita->receta->id,
            'notas'=>$cita->receta->notas,
            'items'=>$cita->receta->items->map(function($it){
                return [
                    'id'=>$it->id,
                    'medicamento'=>$it->medicamento,
                    'dosis'=>$it->dosis,
                    'notas'=>$it->notas,
                ];
            })
        ] : null,
    ]);
});

// Guardar/actualizar receta y diagnostico de una cita
Route::middleware('auth:sanctum')->post('/citas/{id}/receta', function(Request $request, $id) {
    $cita = \App\Models\Cita::with('receta.items')->find($id);
    if (!$cita) {
        return response()->json(['success'=>false,'message'=>'Cita no encontrada'],404);
    }

    $data = $request->validate([
        'diagnostico' => 'nullable|string',
        'notas' => 'nullable|string',
        'items' => 'nullable|array',
        'items.*.medicamento' => 'required_with:items|string|max:150',
        'items.*.dosis' => 'required_with:items|string|max:200',
        'items.*.notas' => 'nullable|string|max:250',
    ]);

    // Actualizar diagnóstico si viene
    if (array_key_exists('diagnostico',$data)) {
        $cita->diagnostico = $data['diagnostico'];
        $cita->save();
    }

    // Crear / actualizar receta
    $receta = $cita->receta;
    if (!$receta) {
        $receta = \App\Models\Receta::create([
            'cita_id' => $cita->id,
            'notas' => $data['notas'] ?? null,
        ]);
    } else {
        if (array_key_exists('notas',$data)) {
            $receta->notas = $data['notas'];
            $receta->save();
        }
        // Limpiar items si se envían nuevos
        if (isset($data['items'])) {
            $receta->items()->delete();
        }
    }

    if (isset($data['items'])) {
        foreach ($data['items'] as $item) {
            $receta->items()->create([
                'medicamento' => $item['medicamento'],
                'dosis' => $item['dosis'],
                'notas' => $item['notas'] ?? null,
            ]);
        }
    }

    $receta->load('items');

    return response()->json([
        'success'=>true,
        'cita_id'=>$cita->id,
        'diagnostico'=>$cita->diagnostico,
        'receta'=>[
            'id'=>$receta->id,
            'notas'=>$receta->notas,
            'items'=>$receta->items->map(function($it){
                return [
                    'id'=>$it->id,
                    'medicamento'=>$it->medicamento,
                    'dosis'=>$it->dosis,
                    'notas'=>$it->notas,
                ];
            })
        ]
    ]);
});

// Actualizar una cita (PATCH/PUT parcial)
Route::middleware('auth:sanctum')->match(['put','patch'], '/citas/{id}', function(Request $request, $id) {
    $cita = Cita::find($id);
    if (!$cita) {
        return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
    }

    $user = $request->user();
    $mascota = Mascota::find($cita->mascota_id);
    $ownerId = $mascota->user_id ?? null;

    // Permitir modificar si es quien creó la cita o el dueño de la mascota
    if ($user->id !== $cita->creada_por && $user->id !== $ownerId) {
        return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
    }

    $request->validate([
        'clinica_id' => 'sometimes|exists:clinicas,id',
        'servicio_id' => 'sometimes|exists:servicios,id',
        'mascota_id' => 'sometimes|exists:mascotas,id',
        'fecha' => 'sometimes|date|after:today',
        'notas' => 'nullable|string|max:500',
        'status' => 'sometimes|string',
    ]);

    $updateData = $request->only(['clinica_id','servicio_id','mascota_id','fecha','notas','status']);
    $updateData = array_filter($updateData, fn($v) => !is_null($v));

    $original = $cita->getOriginal();
    $cita->update($updateData);

    $changed = [];
    foreach ($updateData as $k => $v) {
        if (!array_key_exists($k, $original) || $original[$k] !== $v) {
            $changed[] = $k;
        }
    }

    ActivityLogger::log($request, 'Actualizar cita', 'Cita', $cita->id, [
        'changed_fields' => $changed,
    ], $user->id);

    return response()->json(['success' => true, 'cita' => $cita]);
});

// Eliminar una cita
Route::middleware('auth:sanctum')->delete('/citas/{id}', function(Request $request, $id) {
    $cita = Cita::find($id);
    if (!$cita) {
        return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
    }

    $user = $request->user();
    $mascota = Mascota::find($cita->mascota_id);
    $ownerId = $mascota->user_id ?? null;

    if ($user->id !== $cita->creada_por && $user->id !== $ownerId) {
        return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
    }

    ActivityLogger::log($request, 'Eliminar cita', 'Cita', $cita->id, [], $user->id);
    try {
        $cita->delete();
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => 'Error eliminando la cita'], 500);
    }

    return response()->json(['success' => true, 'message' => 'Cita eliminada correctamente']);
});

// Cancelar todas las citas pasadas no atendidas (pendiente/confirmada -> cancelada)
Route::middleware('auth:sanctum')->post('/citas/cancelar-pasadas', function(Request $request) {
    $user = $request->user();
    // Solo admin (rol A) puede ejecutar
    if (!$user || $user->rol !== 'A') {
        return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
    }

    $ahora = Carbon::now();
    $citas = Cita::where('fecha', '<', $ahora)
        ->whereNotIn('status', ['completada', 'cancelada'])
        ->get();

    $total = $citas->count();
    $canceladas = 0;
    $ids = [];
    foreach ($citas as $cita) {
        $cita->status = 'cancelada';
        $cita->save();
        $canceladas++;
        $ids[] = $cita->id;
        ActivityLogger::log($request, 'Cancelar cita pasada', 'Cita', $cita->id, [
            'motivo' => 'Auto-cancelación por fecha pasada',
            'fecha' => $cita->fecha,
        ], $user->id);
    }

    return response()->json([
        'success' => true,
        'total_revisadas' => $total,
        'total_canceladas' => $canceladas,
        'ids_canceladas' => $ids,
    ]);
});


//obtener clinicas
Route::get('/clinicas', function(){
    $clinicas = \App\Models\Clinica::all();
    return response()->json([
        'success' => true,
        'clinicas' => $clinicas
    ]);
});

//obtener servicios
Route::get('/servicios', function(){
    $servicios = \App\Models\Servicio::all();
    return response()->json([
        'success' => true,
        'servicios' => $servicios
    ]);
});


//subir imagenes
Route::middleware('auth:sanctum')->post('/upload-image', function(Request $request){
    $request->validate([
        'image' => 'required|image|max:2048', // max 2MB
    ]);


    $file = $request->file('image');
    $filename = time() . '.' . $file->getClientOriginalExtension();
    $file->move(public_path('uploads/mascotas'), $filename);

    return response()->json([
        'message' => 'Imagen subida exitosamente',
        'filename' => $filename
    ]);
});





Route::post('/auth/google', [AuthController::class, 'loginWithGoogle']);

// ================== RESTABLECIMIENTO DE CONTRASEÑA POR CÓDIGO ==================
// 1. Solicitar código: envía un correo con un código de 6 dígitos si el email existe
Route::post('/password/forgot', function (Request $request) {
    $request->validate([
        'email' => 'required|email'
    ]);

    $user = User::where('email', $request->email)->first();

    // Siempre responder 200 para no permitir enumeración de correos
    if (!$user) {
        return response()->json([
            'message' => 'Si el correo existe, se enviará un código de verificación.'
        ]);
    }

    // Generar código 6 dígitos
    $code = (string) random_int(100000, 999999);
    $expiresAt = now()->addMinutes(15);

    // Limpiar códigos anteriores del mismo email (opcionales / expirados)
    DB::table('password_reset_tokens')->where('email', $user->email)->delete();

    // Guardar el código en la tabla existente reutilizando el campo token
    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => $code,
        'created_at' => now(),
    ]);

    // Enviar correo
    $user->notify(new ResetPasswordCodeNotification($code));

    return response()->json([
        'message' => 'Si el correo existe, se enviará un código de verificación.'
    ]);
});

// 2. Verificar código (paso intermedio antes de permitir establecer la nueva contraseña)
Route::post('/password/verify-code', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json(['message' => 'Datos inválidos'], 422);
    }

    $record = DB::table('password_reset_tokens')
        ->where('email', $user->email)
        ->where('token', $request->code)
        ->first();

    if (!$record) {
        return response()->json(['message' => 'Código inválido'], 422);
    }

    $created = \Carbon\Carbon::parse($record->created_at);
    if ($created->addMinutes(15)->isPast()) {
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        return response()->json(['message' => 'Código expirado'], 422);
    }

    return response()->json([
        'success' => true,
        'message' => 'Código válido'
    ]);
});

// 3. Restablecer contraseña usando código
Route::post('/password/reset', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string',
        'password' => 'required|string|min:8|confirmed'
    ]);

    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json(['message' => 'Datos inválidos'], 422);
    }

    $record = DB::table('password_reset_tokens')
        ->where('email', $user->email)
        ->where('token', $request->code)
        ->first();

    if (!$record) {
        return response()->json(['message' => 'Código inválido'], 422);
    }

    // Verificar expiración (15 minutos por diseño)
    $created = \Carbon\Carbon::parse($record->created_at);
    if ($created->addMinutes(15)->isPast()) {
        // Borrar código expirado
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        return response()->json(['message' => 'Código expirado'], 422);
    }

    // Actualizar contraseña
    $user->password = Hash::make($request->password);
    $user->save();

    // Eliminar códigos usados
    DB::table('password_reset_tokens')->where('email', $user->email)->delete();

    return response()->json([
        'message' => 'Contraseña restablecida correctamente'
    ]);
});


//editar perfil de usuario  
// ===============================================================================
Route::middleware('auth:sanctum')->put('/perfil', function(Request $request) {
    $user = $request->user();

    $request->validate([
        'nombre' => 'sometimes|required|string|max:100',
        'apellido_paterno' => 'sometimes|required|string|max:100',
        'apellido_materno' => 'sometimes|nullable|string|max:100',
        'fecha_nacimiento' => 'sometimes|required|date',
        'genero' => 'sometimes|required|in:M,F,O',
        'telefono' => 'sometimes|required|string|max:15|unique:users,telefono,' . $user->id,
        'imagen_perfil' => 'sometimes|nullable|string|max:100',
        'password' => 'sometimes|required|string|min:8|confirmed',
    ]);

    $updateData = $request->only([
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'imagen_perfil',
    ]);

    if ($request->filled('password')) {
        $updateData['password'] = Hash::make($request->password);
    }

    // Filtrar claves vacías (no enviadas) para evitar sobreescritura con null accidental
    $updateData = array_filter($updateData, function($value) { return !is_null($value); });

    $original = $user->getOriginal();
    $user->update($updateData);

    // Determinar campos cambiados
    $changed = [];
    foreach ($updateData as $k => $v) {
        if ($k === 'password') { $changed[] = 'password'; continue; }
        if (!array_key_exists($k, $original) || $original[$k] !== $v) {
            $changed[] = $k;
        }
    }

    // Log de actividad: actualización de perfil
    ActivityLogger::log($request, 'Actualizar perfil', 'User', $user->id, [
        'changed_fields' => $changed,
    ], $user->id);

    return response()->json([
        'success' => true,
        'user' => $user
    ]);
});


//eliminar la cuenta de usuario
Route::middleware('auth:sanctum')->delete('/account', function(Request $request) {
    $request->validate([
        'password' => 'required|string'
    ]);

    $user = $request->user();

    // Verificar contraseña actual
    if(!Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Contraseña incorrecta'
        ], 403);
    }

    // Log de actividad antes de eliminar
    ActivityLogger::log($request, 'Eliminar cuenta', 'User', $user->id, [
        'email' => $user->email,
    ], $user->id);

    // Revocar todos los tokens (logout global)
    try { $user->tokens()->delete(); } catch(\Throwable $e) {}

    // Limpiar tokens de recuperación de contraseña asociados
    try { DB::table('password_reset_tokens')->where('email', $user->email)->delete(); } catch(\Throwable $e) {}

    // Eliminar (destruir) usuario definitivamente
    $user->delete();

    return response()->json([
        'success' => true,
        'message' => 'Cuenta eliminada correctamente'
    ], 200);
});


//editar perfil de usuario con POST (multipart/form-data)
Route::middleware('auth:sanctum')->post('/perfil', function(Request $request) {
    $user = $request->user();

    $request->validate([
        'nombre' => 'sometimes|required|string|max:100',
        'apellido_paterno' => 'sometimes|required|string|max:100',
        'apellido_materno' => 'sometimes|nullable|string|max:100',
        'fecha_nacimiento' => 'sometimes|required|date',
        'genero' => 'sometimes|required|in:M,F,O',
        'telefono' => 'sometimes|required|string|max:15|unique:users,telefono,' . $user->id,
        // aceptar tanto string como archivo imagen
        'imagen_perfil' => 'sometimes|nullable',
        'password' => 'sometimes|required|string|min:8|confirmed',
    ]);

    $updateData = $request->only([
        'nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'genero', 'telefono'
    ]);

    // Manejo opcional de imagen_perfil como archivo (si llega como file en multipart)
    if ($request->hasFile('imagen_perfil')) {
        $file = $request->file('imagen_perfil');
        $ext = $file->getClientOriginalExtension();
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $dest = public_path('uploads/perfiles');
        if (!is_dir($dest)) { @mkdir($dest, 0755, true); }
        $file->move($dest, $filename);
        $updateData['imagen_perfil'] = $filename; // guardamos solo el nombre como en mascotas
    } elseif ($request->filled('imagen_perfil') && is_string($request->imagen_perfil)) {
        // Si se envía como string (por ejemplo nombre ya existente) lo usamos directamente
        $updateData['imagen_perfil'] = $request->imagen_perfil;
    }

    if ($request->filled('password')) {
        $updateData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
    }

    // Filtrar nulos para no sobreescribir con null
    $updateData = array_filter($updateData, fn($v) => !is_null($v));

    $original = $user->getOriginal();
    $user->update($updateData);

    $changed = [];
    foreach ($updateData as $k => $v) {
        if ($k === 'password') { $changed[] = 'password'; continue; }
        if (!array_key_exists($k, $original) || $original[$k] !== $v) {
            $changed[] = $k;
        }
    }

    // Log actividad
    ActivityLogger::log($request, 'Actualizar perfil (POST)', 'User', $user->id, [
        'changed_fields' => $changed,
    ], $user->id);

    return response()->json([
        'success' => true,
        'user' => $user,
        'imagen_perfil_url' => $user->imagen_perfil ? asset('uploads/perfiles/' . $user->imagen_perfil) : null,
    ]);
});
// ===============================================================================

// ================== NOTIFICACIONES (LOG DE ENVÍOS) ==================
// Listar notificaciones enviadas al usuario autenticado
Route::middleware('auth:sanctum')->get('/mis-notificaciones', function(Request $request) {
    $user = $request->user();

    $perPage = (int) min(max((int)$request->query('per_page', 20), 1), 100);
    $page = (int) max((int)$request->query('page', 1), 1);
    $afterId = $request->query('after_id'); // para sync incremental: traer > after_id
    $since = $request->query('since'); // fecha ISO opcional
    $q = trim((string)$request->query('q', ''));

    $query = \App\Models\NotificationLog::where('user_id', $user->id);

    if ($afterId) {
        $query->where('id', '>', (int)$afterId);
    }
    if ($since) {
        // Intentar parsear fecha
        try { $sinceDt = \Carbon\Carbon::parse($since); $query->where('created_at', '>=', $sinceDt); } catch(\Throwable $e) {}
    }
    if ($q !== '') {
        $query->where(function($sub) use ($q) {
            $sub->where('title', 'like', "%$q%")
                ->orWhere('body', 'like', "%$q%");
        });
    }

    $total = $query->count();
    $logs = $query->orderByDesc('id')
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get()
        ->map(function($log){
            return [
                'id' => $log->id,
                'title' => $log->title,
                'body' => $log->body,
                'data' => $log->data_json ? json_decode($log->data_json, true) : null,
                'tokens' => $log->tokens_json ? json_decode($log->tokens_json, true) : null,
                'success' => $log->success,
                'fail' => $log->fail,
                'total' => $log->total,
                'created_at' => $log->created_at->toIso8601String(),
            ];
        });

    return response()->json([
        'success' => true,
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'last_page' => (int) ceil($total / $perPage),
        'notificaciones' => $logs,
    ]);
});

// Detalle de una notificación (solo si pertenece al usuario)
Route::middleware('auth:sanctum')->get('/mis-notificaciones/{id}', function(Request $request, $id) {
    $user = $request->user();
    $log = \App\Models\NotificationLog::where('id', $id)->where('user_id', $user->id)->first();
    if (!$log) {
        return response()->json(['success' => false, 'message' => 'No encontrado'], 404);
    }
    return response()->json([
        'success' => true,
        'notificacion' => [
            'id' => $log->id,
            'title' => $log->title,
            'body' => $log->body,
            'data' => $log->data_json ? json_decode($log->data_json, true) : null,
            'tokens' => $log->tokens_json ? json_decode($log->tokens_json, true) : null,
            'success' => $log->success,
            'fail' => $log->fail,
            'total' => $log->total,
            'results' => $log->results_json ? json_decode($log->results_json, true) : null,
            'created_at' => $log->created_at->toIso8601String(),
        ]
    ]);
});
// ================================================================

// Listar horarios disponibles por hora para una clínica y fecha (YYYY-MM-DD)
Route::get('/horarios-disponibles', function(Request $request) {
    $request->validate([
        'clinica_id' => 'required|exists:clinicas,id',
        'fecha' => 'required|date_format:Y-m-d',
    ]);

    $clinicaId = (int) $request->query('clinica_id');
    $fechaStr = $request->query('fecha');

    // Determinar día de la semana (0=domingo ... 6=sábado) usando timezone de la app
    $fecha = Carbon::createFromFormat('Y-m-d', $fechaStr, config('app.timezone'));
    $dow = (int) $fecha->dayOfWeek; // 0..6

    // Obtener horario de la clínica para ese día
    $horario = Horario::where('clinica_id', $clinicaId)
        ->where('dia_semana', $dow)
        ->first();

    if (!$horario) {
        return response()->json([
            'success' => true,
            'clinica_id' => $clinicaId,
            'fecha' => $fechaStr,
            'slots' => [],
            'message' => 'La clínica no tiene horario configurado para ese día',
        ]);
    }

    // Se asume columnas: hora_inicio (HH:MM:SS), hora_fin (HH:MM:SS)
    $inicio = Carbon::parse($fechaStr . ' ' . $horario->hora_inicio, config('app.timezone'));
    $fin = Carbon::parse($fechaStr . ' ' . $horario->hora_fin, config('app.timezone'));

    if ($fin->lessThanOrEqualTo($inicio)) {
        return response()->json([
            'success' => true,
            'clinica_id' => $clinicaId,
            'fecha' => $fechaStr,
            'slots' => [],
            'message' => 'Horario inválido (fin <= inicio)',
        ]);
    }

    // Generar slots por cada hora completa [inicio, fin)
    $slots = [];
    $horas = [];
    $cursor = $inicio->copy()->minute(0)->second(0);
    if ($cursor->lt($inicio)) { $cursor->addHour(); }

    while ($cursor->lt($fin)) {
        $slotInicio = $cursor->copy();
        $slotFin = $cursor->copy()->addHour();
        if ($slotFin->gt($fin)) { break; }

        // Verificar si ya existe una cita que ocupe exactamente esa hora de inicio
        $existe = Cita::where('clinica_id', $clinicaId)
            ->whereDate('fecha', $fechaStr)
            ->whereTime('fecha', $slotInicio->format('H:i:s'))
            ->exists();

        if (!$existe) {
            $slots[] = [
                'inicio' => $slotInicio->format('Y-m-d H:00:00'),
                'fin' => $slotFin->format('Y-m-d H:00:00'),
                'label' => $slotInicio->format('H:00') . ' - ' . $slotFin->format('H:00'),
            ];
            // también listado simple de horas (ej. 9,10,11)
            $horas[] = (int) $slotInicio->format('H');
        }

        $cursor->addHour();
    }

    return response()->json([
        'success' => true,
        'clinica_id' => $clinicaId,
        'fecha' => $fechaStr,
        'slots' => $slots,
        'horas' => $horas,
    ]);
});

//api para traer citas y recetas de una mascota, citas completadas con receta
Route::middleware('auth:sanctum')->get('/citas-recetas-mascota/{id}', function(Request $request, $id){
    $mascota = Mascota::find($id);
    if (!$mascota) {
        return response()->json(['message' => 'Mascota no encontrada'], 404);
    }
    $citas = Cita::with('receta.items')
        ->where('mascota_id', $mascota->id)
        ->where('status', 'completada')
        ->whereHas('receta')
        ->orderBy('fecha', 'desc')
        ->get();
    return response()->json([
        'success' => true,
        'citas' => $citas
    ]);
});


//añadir dispensador
Route::middleware('auth:sanctum')->post('/dispensadores', function(Request $request, $id){
    $request->validate([
        'nombre' => 'required|string|max:100',
        'ubicacion' => 'nullable|string|max:255',
    ]);

    $dispensador = Dispensador::create([
        'nombre' => $request->nombre,
        'ubicacion' => $request->ubicacion,
        'creado_por' => $request->user()->id,
    ]);

    return response()->json([
        'success' => true,
        'dispensador' => $dispensador
    ]);
});