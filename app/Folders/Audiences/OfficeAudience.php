<?php

namespace App\Folders\Audiences;

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Share with the active members of an institutional office (office_user pivot,
 * is_active = true). Mirrors how Forms routing scopes office membership.
 */
class OfficeAudience implements FolderAudience
{
    public function type(): string { return 'office'; }
    public function label(): string { return 'Office'; }
    public function icon(): string { return 'fa-briefcase'; }

    public function options(): Collection
    {
        return Office::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            ->map(fn ($o) => ['value' => (string) $o->id, 'label' => $o->name]);
    }

    public function valueLabel(string $value): ?string
    {
        return optional(Office::find($value))->name;
    }

    public function userValues(User $user): array
    {
        return $user->activeOffices()->pluck('offices.id')->map(fn ($id) => (string) $id)->all();
    }

    public function membersQuery(string $value): Builder
    {
        // Active membership only — resolved against the pivot directly so the
        // is_active flag is honoured regardless of relation eager-loading.
        return User::query()->where('is_approve', true)
            ->whereIn('id', function ($q) use ($value) {
                $q->select('user_id')->from('office_user')
                    ->where('office_id', $value)
                    ->where('is_active', true);
            });
    }
}
