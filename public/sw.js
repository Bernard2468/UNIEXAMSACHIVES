/*
 * UDTS Service Worker — Web Push notifications.
 *
 * Lifecycle:
 *   install   → skip waiting, take over immediately
 *   activate  → claim all open tabs
 *   push      → render the OS notification
 *   notificationclick → open / focus the right URL
 *
 * This SW lives at the SITE ROOT (/sw.js) so its scope covers every dashboard
 * route. If you move this file into /js/ or elsewhere, push will only work
 * for pages under that subpath.
 */

const SW_VERSION = 'udts-sw-v2';

// Decode a base64url VAPID key into the Uint8Array the Push API expects.
function urlBase64ToUint8Array(b64) {
    const padding = '='.repeat((4 - (b64.length % 4)) % 4);
    const base64 = (b64 + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);
    const arr = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; i++) arr[i] = raw.charCodeAt(i);
    return arr;
}

self.addEventListener('install', (event) => {
    // Skip waiting → next page load gets the new SW without an extra refresh.
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// ============================================================
// PUSH RECEIVED — render the OS notification.
// ============================================================
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let payload;
    try { payload = event.data.json(); }
    catch (e) {
        // Some test pushes arrive as raw text — fall back gracefully.
        payload = { title: 'New notification', body: event.data.text() };
    }

    const title = payload.title || 'UDTS';
    const options = {
        body:    payload.body  || '',
        icon:    payload.icon  || '/img/cug_logo_update.jpeg',
        badge:   payload.badge || '/img/favicon.ico',
        // The tag de-duplicates repeated notifications for the same item.
        // If the same notification fires twice, the OS replaces instead of stacking.
        tag:     payload.tag   || ('udts-' + Date.now()),
        renotify: false,
        requireInteraction: false,
        // Vibration pattern: short-pause-short. Mobile only — silently ignored on desktop.
        vibrate: [120, 60, 120],
        data: {
            url:             payload.url || '/',
            notification_id: payload.data && payload.data.notification_id,
            category:        payload.data && payload.data.category,
            type:            payload.data && payload.data.type,
        },
        actions: payload.actions || [],
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// ============================================================
// NOTIFICATION CLICKED — focus an existing tab on the URL, or open one.
// ============================================================
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data && event.notification.data.url || '/';
    // If the user clicked a specific action button, route to a variant.
    let resolvedUrl = targetUrl;
    if (event.action === 'sign') {
        resolvedUrl = targetUrl + '#sign';
    }

    event.waitUntil((async () => {
        const allClients = await clients.matchAll({ type: 'window', includeUncontrolled: true });

        // 1. If a tab is already on this URL → focus it.
        for (const client of allClients) {
            try {
                const url = new URL(client.url);
                const target = new URL(resolvedUrl, self.location.origin);
                if (url.origin === target.origin && url.pathname === target.pathname) {
                    await client.focus();
                    // Soft-update the hash so the page can scroll to the signature.
                    if (target.hash && 'navigate' in client) {
                        return client.navigate(resolvedUrl);
                    }
                    return;
                }
            } catch (e) { /* malformed client URL — skip */ }
        }

        // 2. Otherwise → focus any open tab and navigate it.
        for (const client of allClients) {
            if ('focus' in client) {
                await client.focus();
                if ('navigate' in client) {
                    try { return client.navigate(resolvedUrl); } catch (e) {}
                }
                break;
            }
        }

        // 3. No open tab at all → open a fresh window.
        if (clients.openWindow) {
            return clients.openWindow(resolvedUrl);
        }
    })());
});

// ============================================================
// PUSH SUBSCRIPTION RENEWAL — when the browser rotates the subscription key.
// ============================================================
self.addEventListener('pushsubscriptionchange', (event) => {
    event.waitUntil((async () => {
        try {
            // Recover the applicationServerKey: prefer the old subscription's
            // key; if the browser didn't hand us one, fetch the current VAPID
            // public key from the backend and decode it. Without a key the
            // re-subscribe silently produces an unusable subscription.
            let appServerKey = event.oldSubscription && event.oldSubscription.options
                ? event.oldSubscription.options.applicationServerKey
                : null;

            if (!appServerKey) {
                try {
                    const cfg = await fetch('/dashboard/push/config', { credentials: 'include' }).then(r => r.json());
                    if (cfg && cfg.vapid_public_key) {
                        appServerKey = urlBase64ToUint8Array(cfg.vapid_public_key);
                    }
                } catch (e) { /* fall through — handled below */ }
            }
            if (!appServerKey) return;

            const newSubscription = await self.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: appServerKey,
            });

            // Post the shape the controller validates ({endpoint, keys, ...}).
            // CSRF is exempted for this route (it's auth-protected), so this
            // succeeds where the previous tokenless POST got a 419.
            const json = newSubscription.toJSON();
            await fetch('/dashboard/push/subscribe', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    endpoint:        json.endpoint,
                    keys:            json.keys,
                    contentEncoding: (newSubscription.options && newSubscription.options.applicationServerKey) ? 'aes128gcm' : 'aesgcm',
                }),
            });
        } catch (e) {
            // Best-effort. The on-load self-heal in push-subscriber.blade.php
            // re-registers on the user's next visit if this ever fails.
        }
    })());
});
