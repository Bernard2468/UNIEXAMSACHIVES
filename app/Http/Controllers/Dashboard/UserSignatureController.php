<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Lets a user save (and replace) a personal signature so they don't have
 * to re-draw it for every form. The saved image is stored on the public
 * disk and reused by the in-app signature provider when the user ticks
 * "use my saved signature".
 */
class UserSignatureController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'signature_data' => 'required|string',
        ]);

        $saved = UserSignature::saveFromBase64(Auth::id(), $data['signature_data']);
        if (!$saved) {
            return back()->withErrors(['signature_data' => 'Invalid signature image.']);
        }

        return back()->with('success', 'Signature saved.');
    }

    public function destroy()
    {
        $user = Auth::user();
        $existing = $user->savedSignature;
        if ($existing) {
            $disk = Storage::disk('public');
            if ($existing->signature_image_path && $disk->exists($existing->signature_image_path)) {
                $disk->delete($existing->signature_image_path);
            }
            $existing->delete();
        }
        return back()->with('success', 'Saved signature removed.');
    }
}
