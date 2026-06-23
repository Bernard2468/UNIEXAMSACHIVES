<?php

namespace App\Folders\Audiences;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Share with everyone in the institution (every approved account). Stored with
 * an empty value; matches every user. Notifications are intentionally skipped
 * for this audience — see FoldersController::notifyAudienceMembers().
 */
class EveryoneAudience implements FolderAudience
{
    public function type(): string { return 'everyone'; }
    public function label(): string { return 'Everyone'; }
    public function icon(): string { return 'fa-globe'; }

    public function options(): Collection
    {
        return collect([['value' => '', 'label' => 'Everyone in the institution']]);
    }

    public function valueLabel(string $value): ?string
    {
        return 'Everyone in the institution';
    }

    public function userValues(User $user): array
    {
        // Empty string is the single canonical value for this audience, so every
        // user matches an 'everyone' grant.
        return [''];
    }

    public function membersQuery(string $value): Builder
    {
        return User::query()->where('is_approve', true);
    }
}
