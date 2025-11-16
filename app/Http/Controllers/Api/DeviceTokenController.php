<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DeviceToken;
use Carbon\Carbon;

class DeviceTokenController extends Controller
{
    // Upsert device token for authenticated user
    public function store(Request $request)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json(['message'=>'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'token' => 'required|string|max:512',
            'platform' => 'nullable|string|max:32',
            'device_id' => 'nullable|string|max:128',
            'app_version' => 'nullable|string|max:64',
            'lang' => 'nullable|string|max:8',
        ]);

        $now = Carbon::now();

        $dt = DeviceToken::updateOrCreate(
            ['user_id' => $user->id, 'token' => $data['token']],
            [
                'platform' => $data['platform'] ?? null,
                'device_id' => $data['device_id'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'lang' => $data['lang'] ?? null,
                'last_seen_at' => $now,
            ]
        );

        return response()->json(['ok'=>true,'device_token'=>$dt], 200);
    }

    // Delete token by value
    public function destroy(Request $request, $token = null)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json(['message'=>'Unauthenticated'], 401);
        }

        $tokenValue = $token ?? $request->input('token');
        if(!$tokenValue){
            return response()->json(['message'=>'token is required'], 422);
        }

        $deleted = DeviceToken::where('user_id', $user->id)->where('token', $tokenValue)->delete();

        return response()->json(['ok' => true, 'deleted' => (bool)$deleted]);
    }
}
