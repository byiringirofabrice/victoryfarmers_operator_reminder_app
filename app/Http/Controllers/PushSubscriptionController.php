<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $user = $request->user();
        $payload = $request->input('subscription');
        $controlRoomId = $request->input('control_room_id'); // numeric id of control room

        if (! $payload || ! isset($payload['endpoint'])) {
            return response()->json(['error' => 'Invalid subscription'], 422);
        }

        $endpoint = $payload['endpoint'];
        $p256dh = $payload['keys']['p256dh'] ?? null;
        $auth = $payload['keys']['auth'] ?? null;
        $encoding = $payload['contentEncoding'] ?? null;

        $sub = PushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'user_id' => $user ? $user->id : null,
                'control_room_id' => $controlRoomId,
                'p256dh' => $p256dh,
                'auth_token' => $auth,
                'content_encoding' => $encoding,
                'subscription' => $payload,
            ]
        );

        return response()->json(['success' => true, 'id' => $sub->id]);
    }

    public function unsubscribe(Request $request)
    {
        $endpoint = $request->input('endpoint');

        if ($endpoint) {
            PushSubscription::where('endpoint', $endpoint)->delete();
        }

        return response()->json(['success' => true]);
    }
}
