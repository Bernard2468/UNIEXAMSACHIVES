<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommitteesController extends Controller
{
    /**
     * Check if user has permission to manage committees
     * Only users who can access Manage Users, Departments, Positions, Payment History can manage committees
     * This uses the same permission check: @unless(auth()->user()->is_admin)
     */
    private function checkAdminPermission()
    {
        if (Auth::user()->is_admin) {
            abort(403, 'Unauthorized access. Only administrators can manage committees and boards.');
        }
    }

    /**
     * Display a listing of committees/boards (Admin only - for management)
     */
    public function index()
    {
        $this->checkAdminPermission();

        $committees = Committee::with('users')->withCount('users')->orderBy('created_at', 'desc')->get();
        $users = User::where('is_approve', true)
                    ->with('position')
                    ->select('id', 'first_name', 'last_name', 'email', 'position_id', 'profile_picture')
                    ->orderBy('first_name')
                    ->get();
        
        return view('admin.committees.index', compact('committees', 'users'));
    }

    /**
     * Display user's own committees/boards (Normal users - view only)
     */
    public function myCommittees()
    {
        $userCommittees = Auth::user()->committees()->with('users')->orderBy('created_at', 'desc')->get();
        
        return view('admin.committees.my-committees', compact('userCommittees'));
    }

    /**
     * Display the specified committee/board details
     */
    public function show(Committee $committee)
    {
        // Check if user belongs to this committee
        if (!Auth::user()->committees->contains($committee->id)) {
            abort(403, 'You do not have access to this committee/board.');
        }

        $committee->load(['users' => function($query) {
            $query->with('position')->orderBy('first_name');
        }]);

        return view('admin.committees.show', compact('committee'));
    }

    /**
     * Store a newly created committee/board
     */
    public function store(Request $request)
    {
        $this->checkAdminPermission();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        Committee::create($request->all());

        return redirect()->route('committees.index')
            ->with('success', 'Committee/Board created successfully.');
    }

    /**
     * Update the specified committee/board
     */
    public function update(Request $request, Committee $committee)
    {
        $this->checkAdminPermission();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $committee->update($request->all());

        return redirect()->route('committees.index')
            ->with('success', 'Committee/Board updated successfully.');
    }

    /**
     * Remove the specified committee/board
     */
    public function destroy(Committee $committee)
    {
        $this->checkAdminPermission();

        $committee->delete();

        return redirect()->route('committees.index')
            ->with('success', 'Committee/Board deleted successfully.');
    }

    /**
     * Add users to a committee/board
     */
    public function addUsers(Request $request, Committee $committee)
    {
        $this->checkAdminPermission();

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $committee->users()->syncWithoutDetaching($request->user_ids);

        return redirect()->back()
            ->with('success', 'Users added to committee/board successfully.');
    }

    /**
     * Remove a user from a committee/board
     */
    public function removeUser(Committee $committee, User $user)
    {
        $this->checkAdminPermission();

        $committee->users()->detach($user->id);

        return redirect()->back()
            ->with('success', 'User removed from committee/board successfully.');
    }
}

