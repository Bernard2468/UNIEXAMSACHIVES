<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SystemLetterhead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SystemLetterheadController extends Controller
{
    /**
     * Only "Admin" tier (DB role `user`) and Super Admins manage letterheads.
     * Mirrors the access pattern used for Manage Users / Positions / etc.
     */
    private function authorizeAccess(): void
    {
        if (!auth()->check() || auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function index()
    {
        $this->authorizeAccess();

        $letterheads = SystemLetterhead::ordered()->get();

        return view('admin.system-letterheads.index', compact('letterheads'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'image'       => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $path = $this->moveImage($request->file('image'));

        $slug = $this->generateUniqueSlug($request->name);

        $maxOrder = (int) SystemLetterhead::max('display_order');

        SystemLetterhead::create([
            'slug'          => $slug,
            'name'          => $request->name,
            'description'   => $request->description,
            'image_path'    => $path,
            'is_active'     => true,
            'display_order' => $maxOrder + 1,
            'uploaded_by'   => auth()->id(),
        ]);

        return redirect()->route('dashboard.system-letterheads.index')
                         ->with('success', 'Letterhead uploaded successfully.');
    }

    public function update(Request $request, SystemLetterhead $system_letterhead)
    {
        $this->authorizeAccess();

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $payload = [
            'name'        => $request->name,
            'description' => $request->description,
        ];

        if ($request->hasFile('image')) {
            $this->deleteLocalImage($system_letterhead->image_path);
            $payload['image_path'] = $this->moveImage($request->file('image'));
        }

        $system_letterhead->update($payload);

        return redirect()->route('dashboard.system-letterheads.index')
                         ->with('success', 'Letterhead updated successfully.');
    }

    public function toggle(SystemLetterhead $system_letterhead)
    {
        $this->authorizeAccess();

        $system_letterhead->update(['is_active' => !$system_letterhead->is_active]);

        $msg = $system_letterhead->is_active
            ? 'Letterhead activated — it will now appear in the memo composer.'
            : 'Letterhead deactivated — it is hidden from the memo composer.';

        return redirect()->route('dashboard.system-letterheads.index')->with('success', $msg);
    }

    public function reorder(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:system_letterheads,id',
        ]);

        foreach ($request->order as $position => $id) {
            SystemLetterhead::where('id', $id)->update(['display_order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(SystemLetterhead $system_letterhead)
    {
        $this->authorizeAccess();

        // Prevent breaking past memos that reference the seeded slugs.
        $referenced = \DB::table('comm_campaigns')
            ->where('letterhead', $system_letterhead->slug)
            ->exists();

        if ($referenced) {
            return redirect()->route('dashboard.system-letterheads.index')
                ->with('error', 'Cannot delete "' . $system_letterhead->name . '": it is still used by one or more memos. Deactivate it instead to hide it from new memos while preserving history.');
        }

        $this->deleteLocalImage($system_letterhead->image_path);

        $system_letterhead->delete();

        return redirect()->route('dashboard.system-letterheads.index')
                         ->with('success', 'Letterhead deleted successfully.');
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'letterhead';
        $slug = $base;
        $i = 1;
        while (SystemLetterhead::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /**
     * Save the uploaded image directly under public/letterheads (no symlink
     * needed — works on shared hosting where symlink() is disabled). Returns
     * the path relative to the public directory, e.g. "letterheads/abc.png".
     */
    private function moveImage($file): string
    {
        $dir = public_path('letterheads');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        return 'letterheads/' . $filename;
    }

    /**
     * Remove a local image file. Skips entries pointing to remote URLs
     * (legacy Cloudinary seeds).
     */
    private function deleteLocalImage(?string $path): void
    {
        if (!$path || preg_match('#^https?://#i', $path)) {
            return;
        }
        $abs = public_path($path);
        if (file_exists($abs)) {
            @unlink($abs);
        }
    }
}
