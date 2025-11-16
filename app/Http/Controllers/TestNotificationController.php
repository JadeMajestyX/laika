<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;

class TestNotificationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin();
        // Opcional: filtrar por email
        $email = trim($request->query('email',''));
        $query = DeviceToken::with('user')->orderByDesc('updated_at');
        if($email !== ''){
            $query->whereHas('user', function($q) use ($email){
                $q->where('email','like','%'.$email.'%');
            });
        }
        $tokens = $query->limit(300)->get();
        $usuarios = $tokens->pluck('user')->unique('id')->values();
        return view('test_notificaciones', [
            'tokens' => $tokens,
            'usuarios' => $usuarios,
            'email' => $email,
            'usuario' => Auth::user(),
        ]);
    }

    public function send(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'mode' => 'required|in:user,token,topic',
            'user_id' => 'nullable|integer|exists:users,id',
            'token' => 'nullable|string',
            'topic' => 'nullable|string|max:200',
            'title' => 'required|string|max:150',
            'body' => 'required|string|max:500',
            'screen' => 'nullable|string|max:100',
            'extra_id' => 'nullable|string|max:100',
        ]);

        $fcmServerKey = env('FIREBASE_SERVER_KEY');
        if(!$fcmServerKey){
            return back()->with('error','FIREBASE_SERVER_KEY no configurado en .env');
        }

        $payload = [
            'notification' => [
                'title' => $data['title'],
                'body' => $data['body'],
            ],
            'data' => array_filter([
                'screen' => $data['screen'] ?? null,
                'id' => $data['extra_id'] ?? null,
            ]) ?: new \stdClass(),
        ];

        if($data['mode'] === 'topic'){
            if(empty($data['topic'])) return back()->with('error','Topic requerido');
            $payload['to'] = '/topics/'.$data['topic'];
        } elseif($data['mode'] === 'user'){
            if(empty($data['user_id'])) return back()->with('error','user_id requerido');
            $tokens = DeviceToken::where('user_id',$data['user_id'])->pluck('token')->unique()->values()->all();
            if(empty($tokens)) return back()->with('error','Usuario sin tokens registrados');
            $payload['registration_ids'] = $tokens;
        } else { // token directo
            if(empty($data['token'])) return back()->with('error','token requerido');
            $payload['to'] = $data['token'];
        }

        try {
            $ch = curl_init('https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: key=' . $fcmServerKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $resp = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if(curl_errno($ch)){
                $err = curl_error($ch);
                curl_close($ch);
                return back()->with('error','Error cURL: '.$err);
            }
            curl_close($ch);
            Log::info('Test push sent', ['http_code'=>$http,'response'=>$resp]);
            return back()->with('success','Notificación enviada (HTTP '.$http.')');
        } catch(\Throwable $e){
            Log::error('Error enviando push test: '.$e->getMessage());
            return back()->with('error','Excepción: '.$e->getMessage());
        }
    }

    private function authorizeAdmin(): void
    {
        $u = Auth::user();
        if(!$u || $u->rol !== 'A'){
            abort(403,'No autorizado');
        }
    }
}
