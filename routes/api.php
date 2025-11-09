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

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
       'access_token' => $token,
       'token_type' => 'Bearer',
       'user' => $user
    ]);
});

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
        'especie' => 'required|in:Perro,Gato,Otro',
        'raza' => 'nullable|string|max:100',
        'fecha_nacimiento' => 'required|date',
        'sexo' => 'required|in:M,F,O',
        'peso' => 'required|numeric|min:0|max:200',
        'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // imagen opcional
        'user_id' => 'required|exists:users,id'
    ]);

    // 🔹 Procesar la imagen si viene en el request
    $imagePath = null;
    if ($request->hasFile('imagen')) {
        $extension = $request->file('imagen')->getClientOriginalExtension();
        $nombreArchivo = time() . '_' . uniqid() . '.' . $extension; // nombre único
        $imagePath = $request->file('imagen')->storeAs('mascotas', $nombreArchivo, 'public');
    }

    // 🔹 Crear registro en base de datos
    $mascota = Mascota::create([
        'nombre' => $request->nombre,
        'especie' => $request->especie,
        'raza' => $request->raza,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'sexo' => $request->sexo,
        'peso' => $request->peso,
        'imagen' => $imagePath, // se guarda la ruta de la imagen
        'user_id' => $request->user_id
    ]);

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
            'imagen_url' => $imagePath ? asset('storage/' . $imagePath) : null, // URL accesible
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



//actualizar mascota
Route::middleware('auth:sanctum')->put('/actualizar-mascota/{id}', function(Request $request, $id){
    $mascota = Mascota::find($id);

    if (!$mascota) {
        return response()->json(['message' => 'Mascota no encontrada'], 404);
    }

    $request->validate([
        'nombre' => 'nullable|string|max:100',
        'raza' => 'nullable|string|max:100',
        'fecha_nacimiento' => 'nullable|date',
        'sexo' => 'nullable|string|in:M,F,O',
        'peso' => 'nullable|numeric|min:0|max:200',
        'imagen' => 'nullable|string|max:100',
    ]);

    $mascota->update($request->only([
        'nombre',
        'especie',
        'raza',
        'fecha_nacimiento',
        'sexo',
        'peso',
        'imagen'
    ]));

    return response()->json([
        'message' => 'Mascota actualizada exitosamente',
        'data' => $mascota
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