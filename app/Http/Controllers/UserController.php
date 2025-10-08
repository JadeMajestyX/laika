<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    //numero de usuarios
    public function numberOfUsers() {
        // Lógica para obtener el número de usuarios
        $userCount = \App\Models\User::count();
        return response()->json(['user_count' => $userCount]);
    }
}
