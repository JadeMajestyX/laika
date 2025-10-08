<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        $ultimoMes = Carbon::now()->subMonth()->startOfMonth();

        $data = User::all()->where('created_at', '>=', $ultimoMes)->count();

        return view('dashboard', compact('data'));
    }
}
