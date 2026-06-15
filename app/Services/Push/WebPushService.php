<?php

namespace App\Services\Push;

use App\Models\Notification;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Browser Web Push delivery for in-app Notification rows.
 *
 * Behavior:
 *   - Sends only when VAPID keys are configured AND the recipient user has
 *     push_enabled = true AND at least one push subscription on file.
 *   - Auto-prunes subscriptions the push provider reports as expired (404/410).
 *   - Wraps everything in try/catch — push must NEVER break the workflow.
 */
class WebPushService
{
    /**
     * Send a Notification to all of the recipient user's active subscriptions.
     * The Notification row is the source of truth; this method only mirrors it
     * to the OS-level push channel.
     */
    public function sendForNotification(Notification $notification): void
    {
        if (!$this->isConfigured()) {
            return;
        }

        $user = $notification->user;
        if (!$user || !($user->push_enabled ?? true)) {
            return;
        }

        $subscriptions = PushSubscription::where('user_id', $user->id)->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $webPush = $this->makeClient();

            $payload = $this->buildPayload($notification);
            $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            foreach ($subscriptions as $sub) {
                try {
                    $subscription = Subscription::create([
                        'endpoint'        => $sub->endpoint,
                        'publicKey'       => $sub->p256dh_key,
                        'authToken'       => $sub->auth_key,
                        'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                    ]);
                    $webPush->queueNotification($subscription, $payloadJson);
                } catch (\Throwable $e) {
                    Log::warning('WebPush queue failed', ['sub_id' => $sub->id, 'err' => $e->getMessage()]);
                }
            }

            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                $sub = $subscriptions->firstWhere('endpoint', $endpoint);

                if (!$report->isSuccess()) {
                    $code = $report->getResponse()?->getStatusCode();
                    // 404 / 410 mean the subscription is gone — clean it up so
                    // we don't keep paying for failed sends.
                    if ($sub && in_array($code, [404, 410], true)) {
                        $sub->delete();
                    } else {
                        Log::warning('WebPush send failed', [
                            'endpoint' => $endpoint,
                            'status'   => $code,
                            'reason'   => $report->getReason(),
                        ]);
                    }
                    continue;
                }

                if ($sub) {
                    $sub->forceFill(['last_used_at' => now()])->save();
                }
            }
        } catch (\Throwable $e) {
            Log::error('WebPushService failed', ['err' => $e->getMessage()]);
        }
    }

    /**
     * Send a "you're connected" test push and REPORT the provider's response.
     * Returns ['ok' => bool, 'status' => ?int, 'reason' => ?string] so callers
     * (e.g. push:diagnose) can see whether the push service actually accepted
     * the message — a 201 means accepted, 403 = bad/stale VAPID key, 404/410 =
     * dead endpoint (auto-pruned here, same as the main delivery path).
     */
    public function sendTest(PushSubscription $sub, string $title = 'Notifications enabled', string $body = 'You will now receive updates from UDTS.'): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'status' => null, 'reason' => 'web push not configured'];
        }
        try {
            $client = $this->makeClient();
            $subscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'publicKey'       => $sub->p256dh_key,
                'authToken'       => $sub->auth_key,
                'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
            ]);
            // Use a guarded URL so a missing/renamed route can never throw here
            // (this app's home is the `dashboard` route, not `home`).
            $url = \Illuminate\Support\Facades\Route::has('dashboard') ? route('dashboard') : url('/');
            $report = $client->sendOneNotification($subscription, json_encode([
                'title' => $title,
                'body'  => $body,
                'url'   => $url,
                'tag'   => 'udts-test-' . $sub->id,
            ]));

            $status = $report->getResponse()?->getStatusCode();
            $reason = $report->getReason();

            if ($report->isSuccess()) {
                $sub->forceFill(['last_used_at' => now()])->save();
                return ['ok' => true, 'status' => $status, 'reason' => null];
            }

            Log::warning('WebPush test send rejected', ['sub_id' => $sub->id, 'status' => $status, 'reason' => $reason]);
            if (in_array($status, [404, 410], true)) {
                $sub->delete();
            }
            return ['ok' => false, 'status' => $status, 'reason' => $reason];
        } catch (\Throwable $e) {
            Log::warning('WebPush test send failed', ['err' => $e->getMessage()]);
            return ['ok' => false, 'status' => null, 'reason' => $e->getMessage()];
        }
    }

    protected function isConfigured(): bool
    {
        return class_exists(WebPush::class)
            && (string) config('services.webpush.vapid_public_key')  !== ''
            && (string) config('services.webpush.vapid_private_key') !== '';
    }

    protected function makeClient(): WebPush
    {
        return new WebPush([
            'VAPID' => [
                'subject'    => config('services.webpush.vapid_subject', 'mailto:admin@example.com'),
                'publicKey'  => config('services.webpush.vapid_public_key'),
                'privateKey' => config('services.webpush.vapid_private_key'),
            ],
        ]);
    }

    /**
     * Map a Notification → the JSON payload the service worker will use to
     * render the OS notification (title/body/icon/url/tag/actions).
     */
    protected function buildPayload(Notification $n): array
    {
        return [
            'title'   => $n->title ?: 'New notification',
            'body'    => $n->message ?: '',
            'url'     => $n->url ?: '/',
            'tag'     => 'udts-notif-' . $n->id,    // collapses duplicates per ID
            'icon'    => url('img/cug_logo_update.jpeg'),
            'badge'   => url('img/favicon.ico'),
            'data'    => [
                'notification_id' => $n->id,
                'category'        => $n->resolved_category,
                'type'            => $n->type,
            ],
            'actions' => $this->actionsFor($n),
        ];
    }

    protected function actionsFor(Notification $n): array
    {
        return match ($n->type) {
            'form_assigned'  => [
                ['action' => 'open',  'title' => 'Open form'],
                ['action' => 'sign',  'title' => 'Sign now'],
            ],
            'form_rejected'  => [['action' => 'open', 'title' => 'View feedback']],
            'form_completed' => [['action' => 'open', 'title' => 'View form']],
            default          => [['action' => 'open', 'title' => 'Open']],
        };
    }
}
