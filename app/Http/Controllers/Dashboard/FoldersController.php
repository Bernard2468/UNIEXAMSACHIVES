<?php

namespace App\Http\Controllers\Dashboard;

use App\Folders\Audiences\FolderAudience;
use App\Folders\Audiences\FolderAudienceRegistry;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\File;
use App\Models\Folder;
use App\Models\FolderGrant;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FoldersController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $folders = Folder::where('user_id', $user->id)
            ->withCount(['files', 'exams'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Folders shared with me — directly OR via any group I'm a live member
        // of — each annotated with my effective permission for the role chip.
        $sharedFolders = Folder::sharedListingFor($user);

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

        $folder->load(['files', 'exams', 'members', 'grants']);

        // Group/audience options for the owner's "Share with a group" picker.
        // Built from the registry so new group types appear automatically.
        $audienceTypes = [];
        if ($isOwner) {
            foreach (app(FolderAudienceRegistry::class)->all() as $aud) {
                $audienceTypes[] = [
                    'type' => $aud->type(),
                    'label' => $aud->label(),
                    'icon' => $aud->icon(),
                    'options' => $aud->options()->values()->all(),
                ];
            }
        }

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

        return view('admin.folders.show', compact('folder', 'availableFiles', 'availableExams', 'isOwner', 'audienceTypes'));
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
            ->get(['id', 'first_name', 'last_name', 'email', 'profile_picture', 'position_id', 'department_id']);

        return response()->json([
            'ok' => true,
            'users' => $users->map(function ($u) {
                $full = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: $u->email;
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
            ->get(['users.id', 'first_name', 'last_name', 'email', 'profile_picture', 'position_id', 'department_id']);

        return response()->json([
            'ok' => true,
            'members' => $members->map(function ($u) {
                $full = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: $u->email;
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

    // =================================================================
    //  GROUP / AUDIENCE GRANTS
    // =================================================================

    /**
     * List the group grants on a folder. Owner only. JSON.
     */
    public function grants(Request $request, Folder $folder)
    {
        if (!$folder->isOwnedBy(Auth::user())) {
            return $this->jsonOrAbort($request, 403, 'Only the folder owner can view groups.');
        }

        $grants = $folder->grants()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'ok' => true,
            'grants' => $grants->map(fn (FolderGrant $g) => $this->grantView($g))->all(),
        ]);
    }

    /**
     * Share the folder with a whole group (department, staff category,
     * committee, office, leadership pool, or everyone). Owner only.
     * Membership is resolved live, so the grant follows people in and out of
     * the group automatically.
     */
    public function addGrant(Request $request, Folder $folder)
    {
        if (!$folder->isOwnedBy(Auth::user())) {
            return $this->jsonOrAbort($request, 403, 'Only the folder owner can share with a group.');
        }

        $data = $request->validate([
            'audience_type' => 'required|string|max:32',
            'audience_value' => 'nullable|string|max:191',
            'permission' => 'required|in:viewer,editor',
        ]);

        $registry = app(FolderAudienceRegistry::class);
        $audience = $registry->get($data['audience_type']);
        if (!$audience) {
            return $this->jsonOrAbort($request, 422, 'Unknown group type.');
        }

        // Validate the value against the audience's own allowed options so a
        // forged request cannot grant access to an arbitrary / non-existent
        // group. ('' is the canonical value for the "everyone" audience.)
        $value = (string) ($data['audience_value'] ?? '');
        $allowed = $audience->options()->pluck('value')->map(fn ($v) => (string) $v)->all();
        if (!in_array($value, $allowed, true)) {
            return $this->jsonOrAbort($request, 422, 'That group is not available to share with.');
        }

        $grant = FolderGrant::updateOrCreate(
            [
                'folder_id' => $folder->id,
                'audience_type' => $data['audience_type'],
                'audience_value' => $value,
            ],
            [
                'permission' => $data['permission'],
                'shared_by' => Auth::id(),
            ],
        );

        $this->notifyAudienceMembers($folder, $audience, $value, $data['permission']);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'grant' => $this->grantView($grant)]);
        }
        return back()->with('success', 'Folder shared with ' . $audience->label() . '.');
    }

    /**
     * Remove a group grant. Owner only. (People who joined via this group lose
     * access immediately unless they also have a direct share or another group.)
     */
    public function removeGrant(Request $request, Folder $folder, FolderGrant $grant)
    {
        if (!$folder->isOwnedBy(Auth::user())) {
            return $this->jsonOrAbort($request, 403, 'Only the folder owner can remove groups.');
        }
        if ($grant->folder_id !== $folder->id) {
            return $this->jsonOrAbort($request, 404, 'Group not found on this folder.');
        }

        $grant->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Group access removed.');
    }

    // =================================================================
    //  SHAREABLE LINK
    // =================================================================

    /**
     * Create or rotate the folder's "anyone with the link" token. Owner only.
     * Rotating invalidates any previously-shared link without affecting members
     * who already joined.
     */
    public function shareLink(Request $request, Folder $folder)
    {
        if (!$folder->isOwnedBy(Auth::user())) {
            return $this->jsonOrAbort($request, 403, 'Only the folder owner can manage the share link.');
        }

        $folder->share_token = Str::random(48);
        $folder->share_token_created_at = now();
        $folder->save();

        $url = route('dashboard.folders.join', ['folder' => $folder->id, 'token' => $folder->share_token]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'url' => $url]);
        }
        return back()->with('success', 'Share link ready.');
    }

    /**
     * Turn the share link off. Owner only. Existing members keep their access.
     */
    public function disableShareLink(Request $request, Folder $folder)
    {
        if (!$folder->isOwnedBy(Auth::user())) {
            return $this->jsonOrAbort($request, 403, 'Only the folder owner can manage the share link.');
        }

        $folder->share_token = null;
        $folder->share_token_created_at = null;
        $folder->save();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Share link disabled.');
    }

    /**
     * Open a share link: enrol the logged-in user as a viewer (a real member
     * row, so they stay visible/auditable) and send them into the folder.
     * A wrong/disabled token 404s rather than revealing the folder exists.
     */
    public function joinViaLink(Request $request, Folder $folder, string $token)
    {
        if (empty($folder->share_token) || !hash_equals($folder->share_token, $token)) {
            abort(404);
        }

        $user = Auth::user();

        // Owner or someone who already has access just goes straight in.
        if ($folder->isOwnedBy($user) || $folder->isAccessibleBy($user)) {
            return redirect()->route('dashboard.folders.show', $folder);
        }

        $folder->members()->syncWithoutDetaching([
            $user->id => ['permission' => 'viewer', 'shared_by' => $folder->user_id],
        ]);

        return redirect()->route('dashboard.folders.show', $folder)
            ->with('success', 'You now have access to "' . $folder->name . '".');
    }

    /**
     * Presentation shape for one group grant (label + icon + live member count).
     */
    private function grantView(FolderGrant $grant): array
    {
        $audience = app(FolderAudienceRegistry::class)->get($grant->audience_type);
        $value = (string) $grant->audience_value;

        $memberCount = null;
        if ($audience) {
            try {
                $memberCount = $audience->membersQuery($value)->count();
            } catch (\Throwable $e) {
                $memberCount = null;
            }
        }

        return [
            'id' => $grant->id,
            'type' => $grant->audience_type,
            'type_label' => $audience ? $audience->label() : ucfirst($grant->audience_type),
            'icon' => $audience ? $audience->icon() : 'fa-users',
            'value' => $value,
            'value_label' => $audience ? ($audience->valueLabel($value) ?? '—') : $value,
            'permission' => $grant->permission,
            'member_count' => $memberCount,
        ];
    }

    /**
     * Best-effort in-app notification to the current members of a group when a
     * folder is first shared with it. Capped, and skipped for "everyone" so we
     * never blast the whole institution. New members who join the group later
     * simply find the folder under "Shared with me".
     */
    private function notifyAudienceMembers(Folder $folder, FolderAudience $audience, string $value, string $permission): void
    {
        if ($audience->type() === 'everyone') {
            return;
        }

        try {
            $ownerName = trim((Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? '')) ?: 'Someone';
            $groupLabel = $audience->valueLabel($value) ?? $audience->label();

            $members = $audience->membersQuery($value)
                ->where('id', '!=', Auth::id())
                ->limit(50)
                ->get(['id']);

            foreach ($members as $member) {
                Notification::create([
                    'user_id' => $member->id,
                    'type' => 'folder_share',
                    'title' => 'A folder was shared with your group',
                    'message' => "{$ownerName} shared the folder \"{$folder->name}\" with {$groupLabel} as a " . ucfirst($permission) . '.',
                    'url' => route('dashboard.folders.show', $folder),
                    'is_read' => false,
                    'data' => [
                        'folder_id' => $folder->id,
                        'folder_name' => $folder->name,
                        'shared_by' => Auth::id(),
                        'permission' => $permission,
                        'audience_type' => $audience->type(),
                        'audience_value' => $value,
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Folder group-share notification failed: ' . $e->getMessage());
        }
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