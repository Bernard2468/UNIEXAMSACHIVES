<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Search\GlobalSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Global "search everything" endpoints. Both delegate all visibility rules to
 * GlobalSearchService, which scopes every result to what the current user may
 * already access. The header dropdown calls suggest() (JSON); "See all
 * results" and pressing Enter land on index() (a full page).
 */
class GlobalSearchController extends Controller
{
    public function __construct(private GlobalSearchService $search)
    {
    }

    /**
     * Debounced keystroke endpoint for the header dropdown. Kept cheap: needs
     * >= 2 chars and caps rows per group. Any failure degrades to an empty
     * result set so the header can never throw.
     */
    public function suggest(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'q'           => $q,
                'groups'      => [],
                'total'       => 0,
                'results_url' => route('search.index', ['q' => $q]),
            ]);
        }

        try {
            $groups = $this->search->search(Auth::user(), $q, [], 5);
        } catch (\Throwable $e) {
            report($e);
            $groups = [];
        }

        return response()->json([
            'q'           => $q,
            'groups'      => array_values($groups),
            'total'       => collect($groups)->sum(fn ($g) => count($g['items'])),
            'results_url' => route('search.index', ['q' => $q]),
        ]);
    }

    /**
     * Full results page. Always computes every group (so the type-filter chips
     * can list all matched categories); the optional ?type= only narrows which
     * section the view renders.
     */
    public function index(Request $request)
    {
        $q    = trim((string) $request->input('q', ''));
        $type = $request->input('type');

        $groups = $q !== '' ? $this->search->search(Auth::user(), $q, [], 12) : [];

        return view('admin.search.index', [
            'q'      => $q,
            'groups' => $groups,
            'type'   => is_string($type) && $type !== '' ? $type : null,
            'total'  => collect($groups)->sum(fn ($g) => count($g['items'])),
        ]);
    }
}
