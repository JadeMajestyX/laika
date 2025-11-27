<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use Illuminate\Http\Request;

class AgendarCitaController extends Controller
{
    public function getClinicas(){
        $clinicas = Clinica::where("is_visible", true)->get();

        return json_encode($clinicas);
    }


    public function getServices(Request $request){
        $clinicas = Clinica::where('is_visible', true)->where('id', $request->clinica_id)->with('servicios')->first();
    
        return json_encode($clinicas->servicios);}
}
