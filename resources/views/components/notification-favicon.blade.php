{{--
    Favicon dot + page-title flash.
    Pure frontend. Hooks into the existing pollUnread() flow by listening for
    a custom event 'notification:count' dispatched from updateNotificationBadge().

    Behaviors:
      - When the tab is BACKGROUND and unread > 0: prefix the page title with "(N) ".
        Cleared as soon as the tab regains focus.
      - When unread > 0: repaint the favicon with a red dot overlay (canvas).
        Restored to the original favicon when count returns to 0.

    Pattern: Gmail / Slack / GitHub. Free legitimacy upgrade.
--}}
@if (Auth::check())
<script>
(function () {
    const ORIGINAL_TITLE = document.title;
    let ORIGINAL_FAVICON_HREF = null;
    let currentCount = 0;

    // ---- FAVICON DOT --------------------------------------------------------
    function getFaviconEl() {
        return document.querySelector("link[rel~='icon']")
            || document.querySelector("link[rel~='shortcut icon']");
    }

    function loadImage(src) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload  = () => resolve(img);
            img.onerror = reject;
            img.src = src;
        });
    }

    async function paintFaviconWithDot(count) {
        try {
            const link = getFaviconEl();
            if (!link) return;
            if (!ORIGINAL_FAVICON_HREF) ORIGINAL_FAVICON_HREF = link.href;

            const canvas = document.createElement('canvas');
            canvas.width = 32; canvas.height = 32;
            const ctx = canvas.getContext('2d');

            try {
                const img = await loadImage(ORIGINAL_FAVICON_HREF);
                ctx.drawImage(img, 0, 0, 32, 32);
            } catch (e) {
                ctx.fillStyle = '#1f2937';
                ctx.fillRect(0, 0, 32, 32);
            }

            // Red dot top-right with a white ring (Slack/Gmail pattern).
            ctx.beginPath();
            ctx.arc(24, 8, 8, 0, Math.PI * 2);
            ctx.fillStyle = '#fff';
            ctx.fill();
            ctx.beginPath();
            ctx.arc(24, 8, 6.5, 0, Math.PI * 2);
            const grad = ctx.createLinearGradient(18, 2, 30, 14);
            grad.addColorStop(0, '#ef4444');
            grad.addColorStop(1, '#dc2626');
            ctx.fillStyle = grad;
            ctx.fill();

            // Count text (1 char) — readable up to "9", then "+".
            if (count > 0 && count <= 9) {
                ctx.fillStyle = '#fff';
                ctx.font = 'bold 10px -apple-system, BlinkMacSystemFont, Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(String(count), 24, 9);
            } else if (count > 9) {
                ctx.fillStyle = '#fff';
                ctx.font = 'bold 9px -apple-system, BlinkMacSystemFont, Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('+', 24, 9);
            }

            link.href = canvas.toDataURL('image/png');
        } catch (e) {
            // Favicon painting is best-effort — never break the page.
            console.log('Favicon paint failed', e);
        }
    }

    function restoreFavicon() {
        const link = getFaviconEl();
        if (link && ORIGINAL_FAVICON_HREF) {
            link.href = ORIGINAL_FAVICON_HREF;
        }
    }

    // ---- TITLE FLASH --------------------------------------------------------
    function updateTitle(count) {
        if (count > 0 && document.hidden) {
            const tag = count > 99 ? '(99+)' : `(${count})`;
            document.title = `${tag} ${ORIGINAL_TITLE}`;
        } else {
            document.title = ORIGINAL_TITLE;
        }
    }

    // ---- WIRING -------------------------------------------------------------
    function apply(count) {
        currentCount = Number(count) || 0;
        if (currentCount > 0) {
            paintFaviconWithDot(currentCount);
        } else {
            restoreFavicon();
        }
        updateTitle(currentCount);
    }

    // Listen for the custom event fired by updateNotificationBadge().
    window.addEventListener('notification:count', (e) => {
        apply(e.detail && e.detail.total);
    });

    // When the tab regains focus, drop the (N) prefix immediately.
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) updateTitle(currentCount);
    });

    // Bootstrap on first paint — read the existing badge so the favicon is
    // correct even before pollUnread() runs.
    document.addEventListener('DOMContentLoaded', () => {
        const badge = document.querySelector('.notification-badge');
        if (badge && badge.style.display !== 'none') {
            const txt = (badge.textContent || '').trim();
            const n = txt.endsWith('+') ? 100 : parseInt(txt, 10);
            if (!isNaN(n) && n > 0) apply(n);
        }
    });
})();
</script>
@endif
