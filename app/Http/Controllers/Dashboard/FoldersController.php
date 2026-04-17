<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FoldersController extends Controller
{
    public function index()
    {
        $folders = Folder::where('user_id', Auth::id())
            ->withCount('files')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('admin.folders.index', compact('folders'));
    }

    public function create()
    {
        return view('admin.folders.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $validatedData['user_id'] = Auth::id();

        // Remove password fields from mass-assignment and set hash if provided
        $password = $validatedData['password'] ?? null;
        unset($validatedData['password']);
        unset($validatedData['password_confirmation']);

        $folder = Folder::create($validatedData);

        if (!empty($password)) {
            $folder->password_hash = Hash::make($password);
            $folder->save();
        }

        return redirect()->route('dashboard.folders.show', $folder)
            ->with('success', 'Folder created successfully.');
    }

    public function show(Folder $folder)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to folder.');
        }
        // Gate access if password protected and not recently unlocked
        if (!empty($folder->password_hash)) {
            if (!$this->isFolderRecentlyUnlocked($folder)) {
                return redirect()->route('dashboard.folders.unlock.form', $folder)
                    ->with('info', 'This folder is password protected.');
            }
            // Refresh the last-access timestamp to maintain the 1-minute window
            $this->markFolderUnlockedNow($folder);
        }

        $folder->load('files');
        $availableFiles = File::where('user_id', Auth::id())
            ->where('is_approve', 1)
            ->whereDoesntHave('folders', function ($query) use ($folder) {
                $query->where('folder_id', $folder->id);
            })
            ->get();

        return view('admin.folders.show', compact('folder', 'availableFiles'));
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
        if ($folder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to folder.');
        }

        $validatedData = $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:files,id',
        ]);

        $files = File::whereIn('id', $validatedData['file_ids'])
            ->where('user_id', Auth::id())
            ->where('is_approve', 1)
            ->get();

        $folder->files()->attach($files->pluck('id'));

        return redirect()->route('dashboard.folders.show', $folder)
            ->with('success', count($files) . ' file(s) added to folder successfully.');
    }

    public function removeFile(Folder $folder, File $file)
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to folder.');
        }

        $folder->files()->detach($file->id);

        return redirect()->route('dashboard.folders.show', $folder)
            ->with('success', 'File removed from folder successfully.');
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

    private function isFolderRecentlyUnlocked(Folder $folder): bool
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
}