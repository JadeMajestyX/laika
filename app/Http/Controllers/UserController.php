<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(){
        $usuario = auth()->user();

        $usuarios = User::with('mascotas')->get();

        return view('usuarios', compact('usuario', 'usuarios'));
    }
}
