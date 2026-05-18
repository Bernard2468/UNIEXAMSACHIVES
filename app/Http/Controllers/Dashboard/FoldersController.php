<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\File;
use App\Models\Folder;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FoldersController extends Controller
{
    public function index()
    {
        $folders = Folder::where('user_id', Auth::id())
            ->withCount(['files', 'exams'])
            ->orderBy('created_at', 'desc')
            ->get();

        $sharedFolders = Auth::user()->sharedFolders()
            ->withCount(['files', 'exams'])
            ->with('user:id,first_name,last_name,name,email,profile_picture')
            ->orderBy('folder_shares.created_at', 'desc')
            ->get();

        return view('admin.folders.index', compact('folders', 'sharedFolders'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'password' => 'nullable|string|min:6|confirmed',
            'share_members' => 'nullable|array',
            'share_members.*.user_id' => 'integer|exists:users,id',
            'share_members.*.permission' => 'in:viewer,editor',
        ]);

        $shareMembers = $validatedData['share_members'] ?? [];

        $folderData = [
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'color' => $validatedData['color'],
            'user_id' => Auth::id(),
        ];

        $folder = Folder::create($folderData);

        if (!empty($validatedData['password'])) {
            $folder->password_hash = Hash::make($validatedData['password']);
            $folder->save();
        }

        // Apply any initial share members from the create form.
        $sharedCount = 0;
        foreach ($shareMembers as $row) {
            $uid = (int) ($row['user_id'] ?? 0);
            if (!$uid || $uid === Auth::id()) continue;
            $perm = in_array(($row['permission'] ?? 'viewer'), ['viewer', 'editor'], true) ? $row['permission'] : 'viewer';
            $user = User::find($uid);
            if (!$user) continue;
            $folder->members()->syncWithoutDetaching([
                $uid => ['permission' => $perm, 'shared_by' => Auth::id()],
            ]);
            $this->sendShareNotification($folder, $user, $perm);
            $sharedCount++;
        }

        $message = 'Folder created successfully.';
        if ($sharedCount > 0) {
            $message .= " Shared with {$sharedCount} " . ($sharedCount === 1 ? 'person' : 'people') . '.';
        }

        return redirect()->route('dashboard.folders.show', $folder)
            ->with('success', $message);
    }

    public function show(Folder $folder)
    {
        $user = Auth::user();
        if (!$folder->isAccessibleBy($user)) {
            abort(403, 'You do not have access to this folder.');
        }

        $isOwner = $folder->isOwnedBy($user);

        // Password gate only applies to the owner. For shared members, the
        // share itself IS the access — they should not be prompted.
        if ($isOwner && !empty($folder->password_hash)) {
            if (!$this->isFolderRecentlyUnlocked($folder)) {
                return redirect()->route('dashboard.folders.unlock.form', $folder)
                    ->with('info', 'This folder is password protected.');
            }
            $this->markFolderUnlockedNow($folder);
        }

        $folder->load(['files', 'exams', 'members']);

        // "Available to add" lists are scoped to the current user's own
        // items only — you can only contribute your own uploads.
        $availableFiles = collect();
        $availableExams = collect();
        if ($isOwner || $folder->canEditContents($user)) {
            $availableFiles = File::where('user_id', $user->id)
                ->whereDoesntHave('folders', function ($q) use ($folder) {
                    $q->where('folder_id', $folder->id);
                })
                ->get();
            $availableExams = Exam::where('user_id', $user->id)
                ->whereDoesntHave('folders', function ($q) use ($folder) {
                    $q->where('folder_id', $folder->id);
                })
                ->get();
        }

        return view('admin.folders.show', compact('folder', 'availableFiles', 'availableExams', 'isOwner'));
    }

    public function edit(Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to folder.');
        }

        return view('admin.folders.edit', compact('folder'));
    }

    public function update(Request $request, Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to folder.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $folder->update($validatedData);

        return redirect()->route('dashboard.folders.show', $folder)
            ->with('success', 'Folder updated successfully.');
    }

    public function destroy(Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to folder.');
        }

        $folder->delete();

        return redirect()->route('dashboard.folders.index')
            ->with('success', 'Folder deleted successfully.');
    }

    public function addFiles(Request $request, Folder $folder)
    {
        if (!$folder->canEditContents(Auth::user())) {
            abort(403, 'You do not have permission to add items to this folder.');
        }

        $validatedData = $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:files,id',
        ]);

        $files = File::whereIn('id', $validatedData['file_ids'])
            ->where('user_id', Auth::id())
            ->get();

        $folder->files()->syncWithoutDetaching($files->pluck('id'));

        return redirect()->route('dashboard.folders.show', $folder)
            ->with('success', count($files) . ' file(s) added to folder successfully.');
    }

    public function removeFile(Request $request, Folder $folder, File $file)
    {
        if ($folder->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Unauthorized folder.'], 403);
            }
            abort(403, 'Unauthorized access to folder.');
        }

        $folder->files()->detach($file->id);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'File removed from folder.']);
        }

        return redirect()->route('dashboard.folders.show', $folder)
            ->with('success', 'File removed from folder successfully.');
    }

    public function addExams(Request $request, Folder $folder)
    {
        if (!$folder->canEditContents(Auth::user())) {
            abort(403, 'You do not have permission to add items to this folder.');
        }

        $validatedData = $request->validate([
            'exam_ids' => 'required|array',
            'exam_ids.*' => 'exists:exams,id',
        ]);

        $exams = Exam::whereIn('id', $validatedData['exam_ids'])
            ->where('user_id', Auth::id())
            ->get();

        $folder->exams()->syncWithoutDetaching($exams->pluck('id'));

        return redirect()->route('dashboard.folders.show', $folder)
            ->with('success', count($exams) . ' exam(s) added to folder successfully.');
    }

    public function removeExam(Request $request, Folder $folder, Exam $exam)
    {
        if ($folder->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Unauthorized folder.'], 403);
            }
            abort(403, 'Unauthorized access to folder.');
        }

        $folder->exams()->detach($exam->id);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Exam removed from folder.']);
        }

        return redirect()->route('dashboard.folders.show', $folder)
            ->with('success', 'Exam removed from folder successfully.');
    }

    /**
     * Unified drag-drop endpoint. Accepts JSON body:
     *   { type: 'file'|'exam', item_id: <id> }
     * Validates ownership of folder + item, then attaches.
     */
    public function moveItem(Request $request, Folder $folder)
    {
        if (!$folder->canEditContents(Auth::user())) {
            return response()->json([
                'ok' => false,
                'message' => 'You do not have permission to add items to this folder.',
            ], 403);
        }
        // Password gate only applies to the owner (editors bypass — share IS access).
        if ($folder->isOwnedBy(Auth::user())
            && !empty($folder->password_hash)
            && !$this->isFolderRecentlyUnlocked($folder)) {
            return response()->json(['ok' => false, 'message' => 'Folder is locked. Unlock it first.', 'locked' => true], 423);
        }

        $data = $request->validate([
            'type' => 'required|in:file,exam',
            'item_id' => 'required|integer',
        ]);

        if ($data['type'] === 'file') {
            $file = File::where('id', $data['item_id'])->where('user_id', Auth::id())->first();
            if (!$file) {
                return response()->json(['ok' => false, 'message' => 'File not found.'], 404);
            }
            $folder->files()->syncWithoutDetaching([$file->id]);
            return response()->json(['ok' => true, 'message' => 'File moved into folder.']);
        }

        $exam = Exam::where('id', $data['item_id'])->where('user_id', Auth::id())->first();
        if (!$exam) {
            return response()->json(['ok' => false, 'message' => 'Exam not found.'], 404);
        }
        $folder->exams()->syncWithoutDetaching([$exam->id]);
        return response()->json(['ok' => true, 'message' => 'Exam moved into folder.']);
    }

    public function unlockForm(Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to folder.');
        }
        if (empty($folder->password_hash)) {
            return redirect()->route('dashboard.folders.show', $folder);
        }
        return view('admin.folders.unlock', compact('folder'));
    }

    public function unlock(Request $request, Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to folder.');
        }
        if (empty($folder->password_hash)) {
            return redirect()->route('dashboard.folders.show', $folder);
        }

        $data = $request->validate([
            'password' => 'required|string',
        ]);

        if (Hash::check($data['password'], $folder->password_hash)) {
            $this->markFolderUnlockedNow($folder);
            return redirect()->route('dashboard.folders.show', $folder)
                ->with('success', 'Folder unlocked.');
        }

        return back()->withErrors(['password' => 'Incorrect password.']);
    }

    public function isFolderRecentlyUnlocked(Folder $folder): bool
    {
        $key = $this->getFolderSessionKey($folder);
        $timestamp = session($key);
        if (!$timestamp) {
            return false;
        }
        $threshold = now()->subSeconds(60)->timestamp;
        return $timestamp >= $threshold;
    }

    private function markFolderUnlockedNow(Folder $folder): void
    {
        $key = $this->getFolderSessionKey($folder);
        session([$key => now()->timestamp]);
    }

    private function getFolderSessionKey(Folder $folder): string
    {
        return 'folders.unlocked.' . $folder->id;
    }

    public function security(Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to folder.');
        }
        return view('admin.folders.security', compact('folder'));
    }

    public function updateSecurity(Request $request, Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to folder.');
        }

        $removePassword = $request->boolean('remove_password');
        $returnTo = $request->input('return_to');

        // Build validation rules depending on action
        $rules = [];
        if ($removePassword) {
            // Require current password if folder has password
            if (!empty($folder->password_hash)) {
                $rules['current_password'] = 'required|string';
            }
        } else {
            // Setting or changing password
            $rules['new_password'] = 'required|string|min:6|confirmed';
            if (!empty($folder->password_hash)) {
                $rules['current_password'] = 'required|string';
            }
        }

        $data = $request->validate($rules);

        // If a current password is required, verify it
        if (isset($data['current_password']) && !empty($folder->password_hash)) {
            if (!Hash::check($data['current_password'], $folder->password_hash)) {
                return back()->withErrors(['current_password' => 'Current folder password is incorrect.']);
            }
        }

        if ($removePassword) {
            $folder->password_hash = null;
            $folder->save();
            // Clear any prior unlock stamp
            session()->forget($this->getFolderSessionKey($folder));
            return $returnTo
                ? redirect($returnTo)->with('success', 'Password protection removed.')
                : redirect()->route('dashboard.folders.show', $folder)->with('success', 'Password protection removed.');
        }

        // Change or set new password
        $folder->password_hash = Hash::make($data['new_password']);
        $folder->save();
        // Clear any prior unlock stamp so user must enter new password
        session()->forget($this->getFolderSessionKey($folder));

        // Redirect back to unlock prompt (or provided return URL)
        $defaultUnlock = route('dashboard.folders.unlock.form', $folder);
        return redirect($returnTo ?: $defaultUnlock)->with('success', 'Folder password updated. Please unlock with the new password.');
    }

    // =================================================================
    //  SHARING / MEMBERS
    // =================================================================

    /**
     * Search users to invite. Returns up to 8 lightweight rows.
     * Excludes the current user and (optionally) anyone already shared.
     */
    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $excludeFolder = $request->input('folder_id');

        if (strlen($q) < 2) {
            return response()->json(['ok' => true, 'users' => []]);
        }

        $query = User::query()
            ->where('id', '!=', Auth::id())
            ->where(function ($qq) use ($q) {
                $qq->where('first_name', 'LIKE', "%{$q}%")
                    ->orWhere('last_name', 'LIKE', "%{$q}%")
                    ->orWhere('name', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%");
            });

        if ($excludeFolder) {
            $folder = Folder::find($excludeFolder);
            if ($folder && $folder->user_id === Auth::id()) {
                $existingIds = $folder->members()->pluck('users.id')->all();
                $query->whereNotIn('id', $existingIds);
            }
        }

        $users = $query
            ->with(['position:id,name', 'department:id,name'])
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'name', 'email', 'profile_picture', 'position_id', 'department_id']);

        return response()->json([
            'ok' => true,
            'users' => $users->map(function ($u) {
                $full = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->name ?: $u->email);
                return [
                    'id' => $u->id,
                    'name' => $full,
                    'email' => $u->email,
                    'avatar' => $u->profile_picture
                        ? asset('profile_pictures/' . $u->profile_picture)
                        : asset('profile_pictures/default-profile.png'),
                    'position' => optional($u->position)->name,
                    'department' => optional($u->department)->name,
                ];
            }),
        ]);
    }

    /**
     * Add one or more members to a folder. Owner only.
     * Body: { members: [{ user_id, permission }, ...] }  OR  legacy { user_id, permission }
     */
    public function share(Request $request, Folder $folder)
    {
        if (!$folder->isOwnedBy(Auth::user())) {
            return $this->jsonOrAbort($request, 403, 'Only the folder owner can share.');
        }

        // Accept both an array of members and a single member.
        $payload = $request->input('members');
        if (!is_array($payload) || empty($payload)) {
            $payload = [[
                'user_id' => $request->input('user_id'),
                'permission' => $request->input('permission'),
            ]];
        }

        $added = [];
        foreach ($payload as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            $permission = in_array(($row['permission'] ?? 'viewer'), ['viewer', 'editor'], true)
                ? $row['permission'] : 'viewer';

            if (!$userId || $userId === Auth::id()) continue;
            $user = User::find($userId);
            if (!$user) continue;

            // syncWithoutDetaching with attributes lets us update permission if member already exists.
            $folder->members()->syncWithoutDetaching([
                $userId => ['permission' => $permission, 'shared_by' => Auth::id()],
            ]);
            // If they were already a member, update the permission explicitly:
            $folder->members()->updateExistingPivot($userId, [
                'permission' => $permission,
                'shared_by' => Auth::id(),
            ]);

            $this->sendShareNotification($folder, $user, $permission);

            $added[] = ['id' => $user->id, 'permission' => $permission];
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'added' => $added]);
        }
        return back()->with('success', count($added) . ' member(s) added to folder.');
    }

    /**
     * Change a member's permission. Owner only.
     */
    public function updateMember(Request $request, Folder $folder, User $user)
    {
        if (!$folder->isOwnedBy(Auth::user())) {
            return $this->jsonOrAbort($request, 403, 'Only the folder owner can change permissions.');
        }

        $data = $request->validate([
            'permission' => 'required|in:viewer,editor',
        ]);

        if (!$folder->members()->where('users.id', $user->id)->exists()) {
            return $this->jsonOrAbort($request, 404, 'User is not a member of this folder.');
        }

        $folder->members()->updateExistingPivot($user->id, [
            'permission' => $data['permission'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Member permission updated.');
    }

    /**
     * Remove a member from a folder. Owner only.
     */
    public function unshare(Request $request, Folder $folder, User $user)
    {
        if (!$folder->isOwnedBy(Auth::user())) {
            return $this->jsonOrAbort($request, 403, 'Only the folder owner can remove members.');
        }

        $folder->members()->detach($user->id);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Member removed from folder.');
    }

    /**
     * The recipient leaves a folder shared with them.
     */
    public function leave(Request $request, Folder $folder)
    {
        $user = Auth::user();
        if ($folder->isOwnedBy($user)) {
            return $this->jsonOrAbort($request, 422, 'Owners cannot leave their own folder. Delete it instead.');
        }
        $folder->members()->detach($user->id);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('dashboard.folders.index')->with('success', 'You left the folder.');
    }

    /**
     * List members of a folder (owner + viewer/editor list). JSON. Owner only.
     */
    public function members(Request $request, Folder $folder)
    {
        if (!$folder->isOwnedBy(Auth::user())) {
            return $this->jsonOrAbort($request, 403, 'Only the folder owner can view members.');
        }

        $members = $folder->members()
            ->with(['position:id,name', 'department:id,name'])
            ->orderBy('folder_shares.created_at', 'desc')
            ->get(['users.id', 'first_name', 'last_name', 'name', 'email', 'profile_picture', 'position_id', 'department_id']);

        return response()->json([
            'ok' => true,
            'members' => $members->map(function ($u) {
                $full = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->name ?: $u->email);
                return [
                    'id' => $u->id,
                    'name' => $full,
                    'email' => $u->email,
                    'avatar' => $u->profile_picture
                        ? asset('profile_pictures/' . $u->profile_picture)
                        : asset('profile_pictures/default-profile.png'),
                    'position' => optional($u->position)->name,
                    'department' => optional($u->department)->name,
                    'permission' => $u->pivot->permission,
                    'shared_at' => optional($u->pivot->created_at)->diffForHumans(),
                ];
            }),
        ]);
    }

    private function sendShareNotification(Folder $folder, User $recipient, string $permission): void
    {
        $ownerName = trim((Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? '')) ?: 'Someone';
        try {
            Notification::create([
                'user_id' => $recipient->id,
                'type' => 'folder_share',
                'title' => 'A folder was shared with you',
                'message' => "{$ownerName} shared the folder \"{$folder->name}\" with you as a " . ucfirst($permission) . '.',
                'url' => route('dashboard.folders.show', $folder),
                'is_read' => false,
                'data' => [
                    'folder_id' => $folder->id,
                    'folder_name' => $folder->name,
                    'shared_by' => Auth::id(),
                    'permission' => $permission,
                ],
            ]);
        } catch (\Throwable $e) {
            // Notifications are best-effort — never block sharing on a notification failure.
            \Log::warning('Folder share notification failed: ' . $e->getMessage());
        }
    }

    private function jsonOrAbort(Request $request, int $status, string $message)
    {
        if ($request->wantsJson()) {
            return response()->json(['ok' => false, 'message' => $message], $status);
        }
        abort($status, $message);
    }
}