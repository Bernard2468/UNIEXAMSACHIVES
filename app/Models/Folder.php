<?php

namespace App\Models;

use App\Folders\Audiences\FolderAudienceRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class Folder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'share_token_created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->belongsToMany(File::class, 'file_folder');
    }

    public function approvedFiles()
    {
        return $this->belongsToMany(File::class, 'file_folder')->where('is_approve', 1);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_folder');
    }

    /**
     * Users this folder has been shared with.
     * Pivot carries: permission ('viewer'|'editor'), shared_by, timestamps.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'folder_shares')
            ->withPivot('permission', 'shared_by')
            ->withTimestamps();
    }

    public function viewers()
    {
        return $this->members()->wherePivot('permission', 'viewer');
    }

    public function editors()
    {
        return $this->members()->wherePivot('permission', 'editor');
    }

    /**
     * Group/audience grants on this folder (department, staff category,
     * committee, office, leadership, everyone). Resolved live — see
     * App\Folders\Audiences.
     */
    public function grants()
    {
        return $this->hasMany(FolderGrant::class);
    }

    /**
     * Owner short-circuits all access checks. Used everywhere.
     */
    public function isOwnedBy(?User $user): bool
    {
        return $user && $user->id === $this->user_id;
    }

    /**
     * Anyone with read access: owner, a direct member (viewer/editor), or a
     * live member of any group this folder is shared with.
     * Sharing intentionally bypasses the folder password — once an owner
     * grants access, the share IS the access. The password is for
     * un-trusted visitors only.
     */
    public function isAccessibleBy(?User $user): bool
    {
        if (!$user) return false;
        if ($this->isOwnedBy($user)) return true;
        if ($this->members()->where('users.id', $user->id)->exists()) return true;
        return $this->matchesAnyGrant($user);
    }

    /**
     * Write access: owner, an explicit editor, or a member of a group granted
     * editor access. Editors can add their OWN files/exams to the folder, but
     * only the owner can remove items or manage members.
     */
    public function canEditContents(?User $user): bool
    {
        if (!$user) return false;
        if ($this->isOwnedBy($user)) return true;
        if ($this->editors()->where('users.id', $user->id)->exists()) return true;
        return $this->matchesAnyGrant($user, 'editor');
    }

    /**
     * Highest permission this user effectively holds: 'editor', 'viewer', or
     * null (no access). Owner counts as 'editor'. Considers both direct shares
     * and group grants, taking the strongest. Used for display (banners/chips).
     */
    public function effectivePermissionFor(?User $user): ?string
    {
        if (!$user) return null;
        if ($this->isOwnedBy($user)) return 'editor';

        $perm = null;

        $direct = $this->members()->where('users.id', $user->id)->first();
        if ($direct) {
            if ($direct->pivot->permission === 'editor') return 'editor';
            $perm = 'viewer';
        }

        $fromGrant = static::matchGrantsPermission($this->grants, static::audienceMembershipMap($user));
        if ($fromGrant === 'editor') return 'editor';
        if ($fromGrant === 'viewer') $perm = $perm ?: 'viewer';

        return $perm;
    }

    /**
     * Does the user belong to any group this folder is shared with?
     * When $requirePermission is given, only grants at that permission count.
     */
    protected function matchesAnyGrant(User $user, ?string $requirePermission = null): bool
    {
        if ($this->grants->isEmpty()) {
            return false;
        }
        $map = static::audienceMembershipMap($user);
        if (empty($map)) {
            return false;
        }
        foreach ($this->grants as $grant) {
            if ($requirePermission && $grant->permission !== $requirePermission) {
                continue;
            }
            if (isset($map[$grant->audience_type])
                && in_array((string) $grant->audience_value, $map[$grant->audience_type], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The current user's live group membership as [audience_type => [values]].
     * Computed once via the registry; safe to reuse across many folders.
     *
     * @return array<string,array<int,string>>
     */
    public static function audienceMembershipMap(User $user): array
    {
        $map = [];
        foreach (app(FolderAudienceRegistry::class)->membershipFor($user) as $m) {
            $map[$m['type']] = $m['values'];
        }

        return $map;
    }

    /**
     * Strongest permission ('editor' > 'viewer' > null) granted to a user by a
     * set of grants, given their precomputed membership map. Pure — no queries.
     *
     * @param  iterable<FolderGrant>  $grants
     * @param  array<string,array<int,string>>  $membershipMap
     */
    public static function matchGrantsPermission($grants, array $membershipMap): ?string
    {
        $perm = null;
        foreach ($grants as $grant) {
            if (isset($membershipMap[$grant->audience_type])
                && in_array((string) $grant->audience_value, $membershipMap[$grant->audience_type], true)) {
                if ($grant->permission === 'editor') {
                    return 'editor';
                }
                $perm = $perm ?: 'viewer';
            }
        }

        return $perm;
    }

    /**
     * Folders shared with this user — via a direct share OR via any group they
     * are a live member of — excluding folders they own. Drives the
     * "Shared with me" listing.
     */
    public function scopeSharedWith(Builder $query, User $user): Builder
    {
        $membership = app(FolderAudienceRegistry::class)->membershipFor($user);

        return $query->where('folders.user_id', '!=', $user->id)
            ->where(function ($q) use ($user, $membership) {
                $q->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')->from('folder_shares')
                        ->whereColumn('folder_shares.folder_id', 'folders.id')
                        ->where('folder_shares.user_id', $user->id);
                });

                if (!empty($membership)) {
                    $q->orWhereExists(function ($sub) use ($membership) {
                        $sub->selectRaw('1')->from('folder_grants')
                            ->whereColumn('folder_grants.folder_id', 'folders.id')
                            ->where(function ($g) use ($membership) {
                                foreach ($membership as $m) {
                                    $g->orWhere(function ($gg) use ($m) {
                                        $gg->where('folder_grants.audience_type', $m['type'])
                                            ->whereIn('folder_grants.audience_value', $m['values']);
                                    });
                                }
                            });
                    });
                }
            });
    }

    /**
     * The "Shared with me" listing for a user: every folder shared directly or
     * via a group, each annotated with `effective_permission` (the strongest of
     * direct vs. group, for the role chip). Single source of truth used by the
     * folders page and every other page that shows a shared-folders strip.
     */
    public static function sharedListingFor(User $user): Collection
    {
        $shared = static::sharedWith($user)
            ->withCount(['files', 'exams'])
            ->with(['user:id,first_name,last_name,email,profile_picture', 'grants'])
            ->orderByDesc('updated_at')
            ->get();

        $membershipMap = static::audienceMembershipMap($user);
        $directPerms = DB::table('folder_shares')
            ->where('user_id', $user->id)
            ->pluck('permission', 'folder_id');

        foreach ($shared as $folder) {
            $perm = $directPerms[$folder->id] ?? null;
            if ($perm !== 'editor') {
                $fromGrant = static::matchGrantsPermission($folder->grants, $membershipMap);
                $perm = $fromGrant === 'editor' ? 'editor' : ($perm ?: $fromGrant);
            }
            $folder->effective_permission = $perm ?: 'viewer';
        }

        return $shared;
    }
}