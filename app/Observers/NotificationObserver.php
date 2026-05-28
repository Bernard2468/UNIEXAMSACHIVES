<?php

namespace App\Observers;

use App\Models\Notification;
use App\Services\Push\WebPushService;

/**
 * Whenever a Notification row is created, mirror it to the user's browser
 * push subscriptions (if any). The observer is intentionally thin: the
 * Notification row itself remains the source of truth — the push is just
 * an OS-level surface for it.
 *
 * Failure mode: WebPushService swallows + logs errors. The observer NEVER
 * throws, so notification creation can't be broken by a push outage.
 */
class NotificationObserver
{
    public function __construct(protected WebPushService $push)
    {
    }

    public function created(Notification $notification): void
    {
        try {
            $this->push->sendForNotification($notification);
        } catch (\Throwable $e) {
            \Log::warning('NotificationObserver push failed', ['err' => $e->getMessage()]);
        }
    }
}
