<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Forms Portal — shows the current user only the submissions they are
 * legitimately involved with. Every tab is hard-scoped by user id; no
 * filter parameter can broaden the result set beyond the user's own data.
 */
class FormPortalController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $user   = Auth::user();

        $tab = $request->input('tab', 'awaiting');
        $allowed = ['awaiting', 'mine', 'signed', 'completed', 'drafts'];
        if (!in_array($tab, $allowed, true)) {
            $tab = 'awaiting';
        }

        $officeIds = $user->activeOffices()->pluck('offices.id')->all();

        $base = FormSubmission::query()
            ->with(['creator', 'currentAssignee', 'currentOffice']);

        switch ($tab) {
            case 'awaiting':
                $base->where('status', FormSubmission::STATUS_IN_PROGRESS)
                     ->where('current_assignee_id', $userId);
                break;

            case 'mine':
                $base->where('created_by', $userId)
                     ->whereIn('status', [
                         FormSubmission::STATUS_IN_PROGRESS,
                         FormSubmission::STATUS_REJECTED,
                     ]);
                break;

            case 'drafts':
                $base->where('created_by', $userId)
                     ->where('status', FormSubmission::STATUS_DRAFT);
                break;

            case 'signed':
                $base->whereHas('signatures', fn ($q) => $q->where('user_id', $userId));
                break;

            case 'completed':
                $base->where('status', FormSubmission::STATUS_COMPLETED)
                     ->where(function ($q) use ($userId, $officeIds) {
                         $q->where('created_by', $userId)
                           ->orWhereHas('signatures', fn ($s) => $s->where('user_id', $userId));
                     });
                break;
        }

        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $base->where(function ($q) use ($like) {
                $q->where('reference', 'like', $like)
                    ->orWhere('title', 'like', $like)
                    ->orWhere('form_code', 'like', $like)
                    ->orWhere('status', 'like', $like)
                    ->orWhereHas('creator', function ($c) use ($like) {
                        $c->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
                    })
                    ->orWhereHas('currentAssignee', function ($a) use ($like) {
                        $a->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
                    })
                    ->orWhereHas('currentOffice', fn ($o) => $o->where('name', 'like', $like));
            });
        }

        $submissions = $base->latest('updated_at')->paginate(15)->appends($request->only(['tab', 'q']));

        $counts = $this->tabCounts($userId, $officeIds);

        return view('admin.forms.portal', [
            'submissions' => $submissions,
            'tab'         => $tab,
            'counts'      => $counts,
            'search'      => $search,
        ]);
    }

    protected function tabCounts(int $userId, array $officeIds): array
    {
        return [
            'awaiting'  => FormSubmission::where('status', FormSubmission::STATUS_IN_PROGRESS)
                ->where('current_assignee_id', $userId)
                ->count(),
            'mine'      => FormSubmission::where('created_by', $userId)
                ->whereIn('status', [FormSubmission::STATUS_IN_PROGRESS, FormSubmission::STATUS_REJECTED])
                ->count(),
            'drafts'    => FormSubmission::where('created_by', $userId)
                ->where('status', FormSubmission::STATUS_DRAFT)
                ->count(),
            'signed'    => FormSubmission::whereHas('signatures', fn ($q) => $q->where('user_id', $userId))
                ->count(),
            'completed' => FormSubmission::where('status', FormSubmission::STATUS_COMPLETED)
                ->where(function ($q) use ($userId) {
                    $q->where('created_by', $userId)
                      ->orWhereHas('signatures', fn ($s) => $s->where('user_id', $userId));
                })
                ->count(),
        ];
    }
}
