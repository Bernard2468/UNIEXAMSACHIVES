<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A live group/audience grant on a folder. See the create_folder_grants
 * migration and App\Folders\Audiences for how (audience_type, audience_value)
 * resolves to a set of users at access time.
 */
class FolderGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'audience_type',
        'audience_value',
        'permission',
        'shared_by',
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function sharedBy()
    {
        return $this->belongsTo(User::class, 'shared_by');
    }
}
