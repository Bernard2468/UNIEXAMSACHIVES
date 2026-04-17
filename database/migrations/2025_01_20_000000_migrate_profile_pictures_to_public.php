<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users with profile pictures
        $users = User::whereNotNull('profile_picture')->get();
        
        foreach ($users as $user) {
            try {
                $oldPath = $user->profile_picture;
                
                // Check if the old path exists in storage
                if (Storage::disk('public')->exists($oldPath)) {
                    // Get the file content
                    $fileContent = Storage::disk('public')->get($oldPath);
                    
                    // Generate new filename
                    $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                    $newFilename = uniqid() . '_' . time() . '.' . $extension;
                    
                    // Save to public directory
                    $publicPath = public_path('profile_pictures/' . $newFilename);
                    File::put($publicPath, $fileContent);
                    
                    // Update user record
                    $user->profile_picture = $newFilename;
                    $user->save();
                    
                    // Delete old file from storage
                    Storage::disk('public')->delete($oldPath);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to migrate profile picture for user ' . $user->id . ': ' . $e->getMessage());
                continue;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it moves files
        // You would need to manually restore from backups if needed
    }
};
