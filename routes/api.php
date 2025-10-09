<?php

use App\Models\CodigoDispensador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Models\Medicion;

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

Route::post('/register-mascota', function(Request $request){
    $request->validate([
        'nombre' => 'required|string|max:100',
        'especie' => 'required|in:Perro,Gato,Otro',
        'raza' => 'nullable|string|max:100',
        'fecha_nacimiento' => 'required|date',
        'sexo' => 'required|in:M,F,O',
        'peso' => 'required|numeric|min:0|max:200',
        'imagen' => 'nullable|string|max:100',
        'user_id' => 'required|exists:users,id'
    ]);

    $mascota = \App\Models\Mascota::create([
        'nombre' => $request->nombre,
        'especie' => $request->especie,
        'raza' => $request->raza,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'sexo' => $request->sexo,
        'peso' => $request->peso,
        'imagen' => $request->imagen,
        'user_id' => $request->user_id
    ]);

    return response()->json([
       'message' => 'Mascota registrada exitosamente',
       'data' => $mascota
    ]);
});