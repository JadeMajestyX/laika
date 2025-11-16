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
use App\Models\Status;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Notifications\ResetPasswordCodeNotification;
use App\Notifications\WelcomeNotification;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\NotificationsController;

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
    $codigo = $request->query('codigo');

    if (!$codigo) {
        return response()->json(['success' => false, 'message' => 'Código no proporcionado'], 400);
    }

    // Buscar el dispensador por su código
    $dispensador = CodigoDispensador::where('codigo', $codigo)->first();

    if (!$dispensador) {
        return response()->json(['success' => false, 'message' => 'Dispensador no encontrado'], 404);
    }

    // Buscar o crear el estado con calibrar=false por defecto
    $statuss = Status::firstOrCreate(
        ['dispensador_id' => $dispensador->id],
        ['status' => false, 'calibrar' => false]
    );

    // Guardamos el estado actual de calibrar antes de modificarlo
    $calibrarActual = (bool) $statuss->calibrar;

    // Si estaba en true, cambiarlo a false y guardar en BD
    if ($calibrarActual === true) {
        $statuss->calibrar = false;
        $statuss->save();
    }

    return response()->json([
        'success' => true,
        'calibrar' => $calibrarActual ? 1 : 0,
        'message' => $calibrarActual ? 'Calibrar cambiado a false' : 'Calibrar ya era false'
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


//activar dispensador
Route::get('/activar-dispensador', function (Request $request) {
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
    // Si el estado era false, cambiarlo a true y guardar en BD
    if ($estadoActual === false) {
        $statuss->status = true;
        $statuss->save();
    }

    return response()->json([
        'success' => true,
        'estado' => $estadoActual ? 1 : 0,
        'message' => $estadoActual ? 'Estado cambiado a true' : 'Estado ya era true'
    ]);
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
            'fecha' => $cita->fecha,
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

    // Crear la cita
    $cita = Cita::create([
        'clinica_id' => $request->clinica_id,
        'servicio_id' => $request->servicio_id,
        'creada_por' => $request->user()->id,
        'mascota_id' => $request->mascota_id,
        'fecha' => $request->fecha,
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
