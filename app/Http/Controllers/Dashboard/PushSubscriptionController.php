<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Services\Push\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Returns the VAPID public key + the user's current opt-in flag and
     * whether they already have at least one device subscribed. The frontend
     * uses this to know which UI state to render (subscribe button vs
     * "Push enabled on this device" check).
     */
    public function config(Request $request)
    {
        $userId = Auth::id();
        return response()->json([
            'vapid_public_key' => (string) config('services.webpush.vapid_public_key'),
            'push_enabled'     => (bool) (Auth::user()->push_enabled ?? true),
            'subscriptions'    => PushSubscription::where('user_id', $userId)->count(),
        ]);
    }

    /**
     * Persist a browser-issued PushSubscription (idempotent — same endpoint
     * for the same user is updated, not duplicated). Triggers a one-off
     * test push so the user sees the new device "come online".
     */
    public function subscribe(Request $request, WebPushService $push)
    {
        $data = $request->validate([
            'endpoint'             => 'required|string|max:2048',
            'keys.p256dh'          => 'required|string|max:255',
            'keys.auth'            => 'required|string|max:100',
            'contentEncoding'      => 'nullable|string|max:32',
        ]);

        $sub = PushSubscription::updateOrCreate(
            [
                'user_id'  => Auth::id(),
                'endpoint' => $data['endpoint'],
            ],
            [
                'p256dh_key'       => $data['keys']['p256dh'],
                'auth_key'         => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? null,
                'user_agent'       => substr((string) $request->userAgent(), 0, 500),
                'last_used_at'     => now(),
            ]
        );

        // Make sure the user's per-account toggle is on after they opt-in.
        $user = Auth::user();
        if (!($user->push_enabled ?? true)) {
            $user->forceFill(['push_enabled' => true])->save();
        }

        $push->sendTest($sub);

        return response()->json(['ok' => true, 'subscription_id' => $sub->id]);
    }

    /**
     * Remove a single subscription (one device). The user_id constraint
     * prevents users from deleting other people's subscriptions even if
     * they know an endpoint URL.
     */
    public function unsubscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint' => 'required|string|max:2048',
        ]);

        PushSubscription::where('user_id', Auth::id())
            ->where('endpoint', $data['endpoint'])
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Toggle the per-account "Browser notifications" preference. When OFF,
     * WebPushService skips delivery for this user across all devices, but
     * the subscriptions remain so re-enabling is one click away.
     */
    public function togglePushEnabled(Request $request)
    {
        $data = $request->validate(['enabled' => 'required|boolean']);

        $user = Auth::user();
        $user->forceFill(['push_enabled' => (bool) $data['enabled']])->save();

        return response()->json(['ok' => true, 'push_enabled' => (bool) $data['enabled']]);
    }
}
