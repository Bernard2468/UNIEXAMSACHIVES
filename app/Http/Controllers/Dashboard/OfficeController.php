<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Manage offices (Finance Office, Internal Audit, Registrar, VC, etc.) and
 * the users that belong to them. Forms route through these offices, so
 * this CRUD is restricted to institutional administrators (UI "Admin"
 * tier = is_admin = false) and Super Admins.
 *
 * Access control is enforced by the `institutional_admin` middleware
 * applied on the route group in routes/web.php — see
 * App\Http\Middleware\InstitutionalAdminMiddleware. (Laravel 11's base
 * Controller no longer extends Illuminate\Routing\Controller, so the
 * legacy $this->middleware() pattern is not available; we gate at the
 * route layer instead.)
 */
class OfficeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $offices = Office::with(['users' => fn ($q) => $q->wherePivot('is_active', true)])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('users', function ($uq) use ($like) {
                            $uq->where('office_user.is_active', true)
                                ->where(function ($n) use ($like) {
                                    $n->where('first_name', 'like', $like)
                                        ->orWhere('last_name', 'like', $like)
                                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
                                });
                        });
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->only('q'));

        return view('admin.offices.index', compact('offices', 'search'));
    }

    public function show(Office $office)
    {
        $office->load(['users']);

        $existingIds = $office->users()->wherePivot('is_active', true)->pluck('users.id');
        $candidates  = User::query()
            ->whereNotIn('id', $existingIds)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email', 'profile_picture']);

        return view('admin.offices.show', compact('office', 'candidates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:120|unique:offices,slug|alpha_dash',
            'description' => 'nullable|string|max:2000',
            'email'       => 'nullable|email|max:255',
        ]);

        $data['slug']      = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = true;

        Office::create($data);

        return redirect()->route('offices.index')
            ->with('success', 'Office created.');
    }

    public function update(Request $request, Office $office)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'email'       => 'nullable|email|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $office->update($data);

        return redirect()->route('offices.show', $office->id)
            ->with('success', 'Office updated.');
    }

    public function destroy(Office $office)
    {
        $officeId   = $office->id;
        $officeName = $office->name;

        try {
            $cancelledCount = DB::transaction(function () use ($office) {
                // Any in-progress submissions still routed here are orphaned
                // (e.g. legacy "HOD / Dean / Director" offices whose stages were
                // replaced by the leadership pool). Cancel them with a clear
                // audit-trail entry so we never leave a form pointing into the void.
                $pending = FormSubmission::where('current_office_id', $office->id)
                    ->where('status', FormSubmission::STATUS_IN_PROGRESS)
                    ->get();

                foreach ($pending as $submission) {
                    $submission->status              = FormSubmission::STATUS_CANCELLED;
                    $submission->current_assignee_id = null;
                    $submission->current_office_id  = null;
                    $submission->appendHistory('cancelled', Auth::id(), [
                        'reason' => "Routed office '{$office->name}' was removed.",
                    ]);
                    $submission->save();
                }

                // Explicitly clear the pivot before deleting the office. The FK
                // already cascades, but doing this here means SQLite installs
                // where PRAGMA foreign_keys is off (rare but possible) still
                // produce a clean delete.
                $office->users()->detach();

                $office->delete();

                return $pending->count();
            });
        } catch (\Throwable $e) {
            Log::error('Office delete failed', [
                'office_id'   => $officeId,
                'office_name' => $officeName,
                'user_id'     => Auth::id(),
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            return back()->with('error',
                "Could not delete '{$officeName}': {$e->getMessage()}");
        }

        $msg = "Office '{$officeName}' deleted.";
        if ($cancelledCount > 0) {
            $msg .= " {$cancelledCount} in-progress form" . ($cancelledCount === 1 ? '' : 's') .
                    " routed here " . ($cancelledCount === 1 ? 'was' : 'were') . " cancelled.";
        }

        return redirect()->route('offices.index')->with('success', $msg);
    }

    public function addMember(Request $request, Office $office)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_head' => 'nullable|boolean',
        ]);

        $isHead = $request->boolean('is_head');

        if ($isHead) {
            $office->users()->updateExistingPivot(
                $office->users()->wherePivot('is_head', true)->pluck('users.id')->all(),
                ['is_head' => false]
            );
        }

        $office->users()->syncWithoutDetaching([
            $data['user_id'] => [
                'is_head'   => $isHead,
                'is_active' => true,
            ],
        ]);

        return back()->with('success', 'Member added to office.');
    }

    public function updateMember(Request $request, Office $office, User $user)
    {
        $request->validate([
            'is_head'   => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $isHead = $request->boolean('is_head');

        if ($isHead) {
            $office->users()->updateExistingPivot(
                $office->users()->wherePivot('is_head', true)->pluck('users.id')->all(),
                ['is_head' => false]
            );
        }

        $office->users()->updateExistingPivot($user->id, [
            'is_head'   => $isHead,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Membership updated.');
    }

    public function removeMember(Office $office, User $user)
    {
        $office->users()->detach($user->id);
        return back()->with('success', 'Member removed.');
    }
}
