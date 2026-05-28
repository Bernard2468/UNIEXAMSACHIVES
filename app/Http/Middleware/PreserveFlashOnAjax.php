<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the flash bag alive across AJAX/JSON requests.
 *
 * THE PROBLEM IT SOLVES:
 *   Laravel's flash mechanism gives flash data exactly ONE follow-up request
 *   to be seen. Any request that completes between the original POST and the
 *   user's next page navigation will silently consume the flash via the
 *   `Store::ageFlashData()` call that fires at session save time.
 *
 *   This bites HARD when the page polls the server periodically (notifications,
 *   chat, presence, etc.). A 2-second polling interval guarantees a poll lands
 *   between the form POST and the redirect target — and by the time the redirect
 *   target renders, session('success') is empty.
 *
 * THE FIX:
 *   For ANY request that returns a JSON payload (polling, mark-as-read, push
 *   subscribe, etc.), call `session()->reflash()` AFTER the controller runs.
 *   `reflash()` moves _flash.old → _flash.new, so the subsequent ageFlashData()
 *   at save time keeps the flash alive for one MORE request — which will be
 *   the user's actual page navigation. The HTML page reads + consumes the
 *   flash normally; subsequent polls reflash a now-empty bag (no-op).
 *
 * SAFE BY DESIGN:
 *   - Only reflashes when the RESPONSE is JSON (so HTML pages still age flash
 *     normally and consume it as expected).
 *   - reflash() is idempotent — re-running it on an empty bag does nothing.
 *   - No effect when there's no flash data to begin with.
 */
class PreserveFlashOnAjax
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $isJsonResponse = $this->isJsonResponse($request, $response);

        if ($isJsonResponse) {
            // The session may not be started for stateless responses; check
            // defensively so we never blow up on routes that opted out.
            $session = $request->session();
            if ($session && method_exists($session, 'reflash')) {
                $session->reflash();
            }
        }

        return $response;
    }

    protected function isJsonResponse(Request $request, Response $response): bool
    {
        // Cheap, robust checks — prefer response Content-Type because some
        // poll requests use bare fetch() without setting X-Requested-With.
        $contentType = (string) $response->headers->get('Content-Type', '');
        if (str_contains($contentType, 'application/json')) {
            return true;
        }
        return $request->ajax() || $request->wantsJson() || $request->expectsJson();
    }
}
