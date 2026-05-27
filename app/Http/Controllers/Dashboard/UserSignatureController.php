<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $raw = $data['signature_data'];
        if (str_starts_with($raw, 'data:')) {
            $commaPos = strpos($raw, ',');
            $raw = $commaPos !== false ? substr($raw, $commaPos + 1) : '';
        }

        $binary = base64_decode($raw, true);
        if ($binary === false || $binary === '') {
            return back()->withErrors(['signature_data' => 'Invalid signature image.']);
        }

        $user = Auth::user();
        $disk = Storage::disk('public');

        $existing = $user->savedSignature;
        if ($existing && $existing->signature_image_path && $disk->exists($existing->signature_image_path)) {
            $disk->delete($existing->signature_image_path);
        }

        $path = 'user-signatures/' . $user->id . '-' . Str::random(8) . '.png';
        $disk->put($path, $binary);

        UserSignature::updateOrCreate(
            ['user_id' => $user->id],
            ['signature_image_path' => $path],
        );

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
