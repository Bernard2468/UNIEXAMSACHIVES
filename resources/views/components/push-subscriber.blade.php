{{--
    Browser Push subscriber + tray toggle handler.
    - Registers /sw.js at site root.
    - Exposes window.udtsPush.{state, subscribe, unsubscribe, refresh}
      so the tray header toggle (built in notification-tray.blade.php) can drive it.
    - Does NOT auto-prompt for permission — users opt in via the tray toggle.
      (Auto-prompting on page load is the #1 reason browsers downgrade or
      hard-deny push permission for a site. Always gate behind a user click.)
--}}
@if (Auth::check())
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const SUPPORTED = ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window);

    // Public API — the tray toggle reads/calls these.
    window.udtsPush = {
        supported: SUPPORTED,
        state: { permission: SUPPORTED ? Notification.permission : 'unsupported', subscribed: false, push_enabled: true, vapid_public_key: '' },
        async refresh()   { return refresh(); },
        async subscribe() { return subscribe(); },
        async unsubscribe(){ return unsubscribe(); },
        async toggle(on)  { return toggle(on); },
    };

    function urlBase64ToUint8Array(b64) {
        const padding = '='.repeat((4 - (b64.length % 4)) % 4);
        const base64 = (b64 + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw = atob(base64);
        const arr = new Uint8Array(raw.length);
        for (let i = 0; i < raw.length; i++) arr[i] = raw.charCodeAt(i);
        return arr;
    }

    async function getRegistration() {
        if (!SUPPORTED) return null;
        // Idempotent — returns the existing registration if /sw.js is already installed.
        return navigator.serviceWorker.register('/sw.js', { scope: '/' });
    }

    async function refresh() {
        if (!SUPPORTED) {
            window.udtsPush.state = { ...window.udtsPush.state, permission: 'unsupported' };
            return window.udtsPush.state;
        }
        try {
            const [cfg, reg] = await Promise.all([
                fetch('/dashboard/push/config', { credentials: 'same-origin' }).then(r => r.json()),
                getRegistration(),
            ]);
            const existing = reg ? await reg.pushManager.getSubscription() : null;
            window.udtsPush.state = {
                permission:       Notification.permission,
                subscribed:       !!existing,
                push_enabled:     !!cfg.push_enabled,
                vapid_public_key: cfg.vapid_public_key || '',
            };
        } catch (e) {
            // Non-fatal — degrade silently so the rest of the tray still works.
            window.udtsPush.state.permission = Notification.permission;
        }
        return window.udtsPush.state;
    }

    async function subscribe() {
        if (!SUPPORTED) return { ok: false, reason: 'unsupported' };

        // Re-fetch the VAPID key in case the user hadn't loaded it yet.
        await refresh();
        const vapid = window.udtsPush.state.vapid_public_key;
        if (!vapid) return { ok: false, reason: 'no_vapid_key' };

        // Permission must be granted via a user gesture — this function is
        // intended to be called from a click handler.
        if (Notification.permission === 'default') {
            const result = await Notification.requestPermission();
            if (result !== 'granted') {
                window.udtsPush.state.permission = result;
                return { ok: false, reason: 'permission_' + result };
            }
        } else if (Notification.permission === 'denied') {
            return { ok: false, reason: 'permission_denied' };
        }

        const reg = await getRegistration();
        let sub = await reg.pushManager.getSubscription();
        if (!sub) {
            sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapid),
            });
        }

        const res = await postSubscription(sub);
        if (!res.ok) return { ok: false, reason: 'server_' + res.status };
        await refresh();
        return { ok: true };
    }

    // Idempotent POST of a browser PushSubscription to the backend. Shared by
    // the opt-in flow AND the on-load self-heal. The server only fires the
    // "Notifications enabled" test push when the row is newly created, so
    // calling this on every page load is silent for already-known devices.
    async function postSubscription(sub) {
        const json = sub.toJSON();
        try {
            const res = await fetch('/dashboard/push/subscribe', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({
                    endpoint:        json.endpoint,
                    keys:            json.keys,
                    contentEncoding: (sub.options && sub.options.applicationServerKey) ? 'aes128gcm' : 'aesgcm',
                }),
            });
            return { ok: res.ok, status: res.status };
        } catch (e) {
            return { ok: false, status: 0 };
        }
    }

    async function unsubscribe() {
        if (!SUPPORTED) return { ok: false };
        const reg = await getRegistration();
        const sub = reg ? await reg.pushManager.getSubscription() : null;
        if (!sub) { await refresh(); return { ok: true }; }

        const endpoint = sub.endpoint;
        try { await sub.unsubscribe(); } catch (e) { /* non-fatal */ }
        await fetch('/dashboard/push/unsubscribe', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ endpoint }),
        });
        await refresh();
        return { ok: true };
    }

    async function toggle(on) {
        await fetch('/dashboard/push/toggle', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ enabled: !!on }),
        });
        await refresh();
        return window.udtsPush.state;
    }

    // SELF-HEAL: re-sync the browser's existing subscription to the server on
    // every load. This is what makes push "persistent" the way big systems do
    // it — if the server ever dropped the row (provider 410, redeploy, key
    // change, DB reset), the browser still holds the subscription and we
    // silently re-register it here. We only re-POST an EXISTING subscription;
    // we never auto-create one (that would resurrect a subscription the user
    // deliberately turned off, since opting out leaves Notification.permission
    // === 'granted').
    async function syncExisting() {
        if (!SUPPORTED) return window.udtsPush.state;
        try {
            const reg = await getRegistration();
            if (Notification.permission === 'granted') {
                const sub = reg ? await reg.pushManager.getSubscription() : null;
                if (sub) await postSubscription(sub);
            }
        } catch (e) { /* non-fatal — degrade silently */ }
        return refresh();
    }
    window.udtsPush.sync = syncExisting;

    document.addEventListener('DOMContentLoaded', () => {
        if (!SUPPORTED) return;
        syncExisting().catch(() => {});
    });
})();
</script>
@endif
