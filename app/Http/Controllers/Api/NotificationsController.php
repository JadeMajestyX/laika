<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use App\Services\FcmV1Client;

class NotificationsController extends Controller
{
    // Internal endpoint to push notifications
    public function push(Request $request)
    {
        // Simple internal auth via header or allow bearer token
        $internalKey = env('INTERNAL_PUSH_KEY');
        $provided = $request->header('X-INTERNAL-KEY');
        if($internalKey && $internalKey !== $provided){
            return response()->json(['message'=>'Forbidden'], 403);
        }

        $data = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'topic' => 'nullable|string',
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:1000',
            'data' => 'nullable|array',
        ]);

        // Modo v1 si está configurado, de lo contrario legacy server key.
        $useV1 = config('fcm.use_v1');
        if($useV1){
            try {
                $client = app(FcmV1Client::class);
            } catch (\Throwable $e){
                return response()->json(['ok'=>false,'message'=>'FCM v1 init error','detail'=>$e->getMessage()], 500);
            }
            if(!empty($data['topic'])){
                $res = $client->sendToTopic($data['topic'], $data['title'], $data['body'], $data['data'] ?? []);
                return response()->json(['ok'=>$res['ok'], 'mode'=>'v1-topic', 'status'=>$res['status'], 'response'=>$res['body']]);
            } elseif(!empty($data['user_id'])) {
                $tokens = DeviceToken::where('user_id', $data['user_id'])->pluck('token')->unique()->values()->all();
                if(empty($tokens)){ return response()->json(['ok'=>false,'message'=>'No device tokens for user'], 404); }
                $results = [];
                $okCount = 0; $failCount = 0;
                foreach($tokens as $tk){
                    try {
                        $r = $client->sendToToken($tk, $data['title'], $data['body'], $data['data'] ?? []);
                        $results[] = ['token'=>$tk,'ok'=>$r['ok'],'status'=>$r['status'],'body'=>$r['body']];
                        $r['ok'] ? $okCount++ : $failCount++;
                    } catch (\Throwable $ex){
                        Log::warning('FCM v1 token error: '.$ex->getMessage());
                        $results[] = ['token'=>$tk,'ok'=>false,'error'=>$ex->getMessage()];
                        $failCount++;
                    }
                }
                return response()->json([
                    'ok' => $failCount === 0,
                    'mode' => 'v1-multi-token',
                    'success' => $okCount,
                    'failed' => $failCount,
                    'results' => $results,
                ], $failCount === 0 ? 200 : 207);
            } else {
                return response()->json(['ok'=>false,'message'=>'Either user_id or topic required'], 422);
            }
        }

        // Legacy FCM server key fallback
        $fcmServerKey = env('FIREBASE_SERVER_KEY');
        if(!$fcmServerKey){
            return response()->json(['ok'=>false,'message'=>'FIREBASE_SERVER_KEY no configurado en .env'], 500);
        }
        $payload = [
            'notification' => ['title'=>$data['title'],'body'=>$data['body']],
            'data' => $data['data'] ?? new \stdClass(),
        ];
        $targets = [];
        if(!empty($data['topic'])){
            $targets['to'] = '/topics/' . $data['topic'];
        } elseif(!empty($data['user_id'])){
            $tokens = DeviceToken::where('user_id', $data['user_id'])->pluck('token')->unique()->values()->all();
            if(empty($tokens)){ return response()->json(['ok'=>false,'message'=>'No device tokens for user'], 404); }
            $targets['registration_ids'] = $tokens;
        } else {
            return response()->json(['ok'=>false,'message'=>'Either user_id or topic required'], 422);
        }
        $body = array_merge($payload, $targets);
        try {
            $ch = curl_init('https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: key=' . $fcmServerKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            $resp = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if(curl_errno($ch)){
                $err = curl_error($ch);
                Log::error('FCM curl error: ' . $err);
                curl_close($ch);
                return response()->json(['ok'=>false,'message'=>'cURL error','detail'=>$err], 500);
            }
            curl_close($ch);
            return response()->json(['ok'=>true,'mode'=>'legacy','http_code'=>$http,'response'=>json_decode($resp, true)]);
        } catch (\Throwable $e){
            Log::error('FCM send error: '.$e->getMessage());
            return response()->json(['ok'=>false,'message'=>'Exception','detail'=>$e->getMessage()], 500);
        }
    }
}
