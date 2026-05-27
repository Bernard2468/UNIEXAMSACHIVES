<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
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
    public function index()
    {
        $offices = Office::with(['users' => fn ($q) => $q->wherePivot('is_active', true)])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.offices.index', compact('offices'));
    }

    public function show(Office $office)
    {
        $office->load(['users']);

        $existingIds = $office->users()->wherePivot('is_active', true)->pluck('users.id');
        $candidates  = User::query()
            ->whereNotIn('id', $existingIds)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

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
        // Safety check: refuse to delete an office that has active form submissions
        // currently routed to it. Reassign or close those forms first.
        $hasActive = $office->pendingSubmissions()->exists();
        if ($hasActive) {
            return back()->with('error', "Cannot delete this office — there are forms currently awaiting action here. Reassign or complete them first.");
        }

        $office->delete();

        return redirect()->route('offices.index')
            ->with('success', 'Office deleted.');
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
