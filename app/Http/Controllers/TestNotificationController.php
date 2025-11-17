<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use App\Services\FcmV1Client;

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

        $useV1 = config('fcm.use_v1');
        $dataPayload = array_filter([
            'screen' => $data['screen'] ?? null,
            'id'     => $data['extra_id'] ?? null,
        ], fn($v) => $v !== null);

        if($useV1){
            try {
                $client = app(FcmV1Client::class);
            } catch (\Throwable $e){
                return back()->with('error','Error inicializando FCM v1: '.$e->getMessage());
            }
            if($data['mode']==='topic'){
                if(empty($data['topic'])) return back()->with('error','Topic requerido');
                $res = $client->sendToTopic($data['topic'], $data['title'], $data['body'], $dataPayload);
                Log::info('Test push v1 topic', $res);
                if($res['ok']){
                    return back()->with('success', 'Enviado (HTTP '.$res['status'].')');
                } else {
                    $err = is_array($res['body']) && isset($res['body']['error']) ? $res['body']['error'] : $res['body'];
                    $detail = [];
                    if(is_array($err)){
                        $detail[] = [
                            'target' => 'topic:'.$data['topic'],
                            'status' => $err['status'] ?? null,
                            'message'=> $err['message'] ?? json_encode($err),
                        ];
                    } else {
                        $detail[] = [ 'target'=>'topic:'.$data['topic'], 'message'=>(string)$err ];
                    }
                    return back()->with('error', 'Fallo (HTTP '.$res['status'].')')
                                 ->with('errors_detail', $detail);
                }
            } elseif($data['mode']==='user') {
                if(empty($data['user_id'])) return back()->with('error','user_id requerido');
                $tokens = DeviceToken::where('user_id',$data['user_id'])->pluck('token')->unique()->values()->all();
                if(empty($tokens)) return back()->with('error','Usuario sin tokens registrados');
                $ok=0;$fail=0; $results=[]; $failReasons=[];
                foreach($tokens as $tk){
                    try { $r=$client->sendToToken($tk, $data['title'], $data['body'], $dataPayload); $results[]=$r; $r['ok']?$ok++:$fail++; }
                    catch(\Throwable $ex){ Log::warning('FCM v1 token error: '.$ex->getMessage()); $results[]=['ok'=>false,'error'=>$ex->getMessage()]; $fail++; }
                    // recolectar motivo si falló
                    $last = end($results);
                    if(!$last['ok']){
                        $body = $last['body'] ?? null;
                        if(is_array($body) && isset($body['error'])){
                            $failReasons[] = [
                                'target' => 'token:'.substr($tk,0,18).'...'
                                            , 'status' => $body['error']['status'] ?? null,
                                'message' => $body['error']['message'] ?? json_encode($body['error']),
                            ];
                        } else {
                            $failReasons[] = [ 'target'=>'token:'.substr($tk,0,18).'...', 'message'=> (is_string($body)?$body:json_encode($body)) ];
                        }
                    }
                }
                $resp = back()->with($fail===0?'success':'error', 'Tokens OK:'.$ok.' Fallos:'.$fail);
                if($fail>0){ $resp->with('errors_detail', $failReasons); }
                return $resp;
            } else { // token directo
                if(empty($data['token'])) return back()->with('error','token requerido');
                $res = $client->sendToToken($data['token'], $data['title'], $data['body'], $dataPayload);
                Log::info('Test push v1 token', $res);
                if($res['ok']){
                    return back()->with('success', 'Enviado (HTTP '.$res['status'].')');
                } else {
                    $err = is_array($res['body']) && isset($res['body']['error']) ? $res['body']['error'] : $res['body'];
                    $detail = [];
                    if(is_array($err)){
                        $detail[] = [ 'target'=>'token:'.substr($data['token'],0,18).'...', 'status'=>$err['status'] ?? null, 'message'=>$err['message'] ?? json_encode($err) ];
                    } else {
                        $detail[] = [ 'target'=>'token:'.substr($data['token'],0,18).'...', 'message'=>(string)$err ];
                    }
                    return back()->with('error', 'Fallo (HTTP '.$res['status'].')')
                                 ->with('errors_detail', $detail);
                }
            }
        }

        // Fallback legacy
        $fcmServerKey = env('FIREBASE_SERVER_KEY');
        if(!$fcmServerKey){
            return back()->with('error','FIREBASE_SERVER_KEY no configurado en .env (modo legacy)');
        }
        $payload = [
            'notification' => [
                'title' => $data['title'],
                'body' => $data['body'],
            ],
            'data' => $dataPayload ?: new \stdClass(),
        ];
        if($data['mode'] === 'topic'){
            if(empty($data['topic'])) return back()->with('error','Topic requerido');
            $payload['to'] = '/topics/'.$data['topic'];
        } elseif($data['mode'] === 'user'){
            if(empty($data['user_id'])) return back()->with('error','user_id requerido');
            $tokens = DeviceToken::where('user_id',$data['user_id'])->pluck('token')->unique()->values()->all();
            if(empty($tokens)) return back()->with('error','Usuario sin tokens registrados');
            $payload['registration_ids'] = $tokens;
        } else {
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
                $err = curl_error($ch); curl_close($ch); return back()->with('error','Error cURL: '.$err);
            }
            curl_close($ch);
            Log::info('Test push legacy', ['http_code'=>$http,'response'=>$resp]);
            $decoded = json_decode($resp, true);
            if(isset($payload['registration_ids'])){
                // multi-token response con results por índice
                $errors = [];
                if(is_array($decoded) && isset($decoded['results'])){
                    foreach($decoded['results'] as $i => $r){
                        if(isset($r['error'])){ $errors[] = ['target'=>'token['.$i.']','message'=>$r['error']]; }
                    }
                }
                $ok = $decoded['success'] ?? null; $fail = $decoded['failure'] ?? null;
                $respBack = back()->with($fail && $fail>0 ? 'error' : 'success', 'Tokens OK:'.($ok ?? 0).' Fallos:'.($fail ?? 0).' (HTTP '.$http.')');
                if(!empty($errors)){ $respBack->with('errors_detail', $errors); }
                return $respBack;
            }
            // token único o topic
            if($http >= 200 && $http < 300){
                return back()->with('success','Notificación enviada (HTTP '.$http.')');
            } else {
                $errDetail = [];
                if(is_array($decoded)){
                    $errDetail[] = ['target'=>'legacy','message'=> isset($decoded['error']) ? $decoded['error'] : json_encode($decoded)];
                } else {
                    $errDetail[] = ['target'=>'legacy','message'=>(string)$resp];
                }
                return back()->with('error','Fallo (HTTP '.$http.')')->with('errors_detail', $errDetail);
            }
        } catch(\Throwable $e){
            Log::error('Error enviando push test legacy: '.$e->getMessage());
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
