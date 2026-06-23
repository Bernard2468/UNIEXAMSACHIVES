<?php

namespace App\Folders\Audiences;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Share with everyone whose primary department matches.
 * Note: a user has a single department_id — see the "teaches in A, takes a
 * course in B" case, which is covered by an additional individual/link share.
 */
class DepartmentAudience implements FolderAudience
{
    public function type(): string { return 'department'; }
    public function label(): string { return 'Department'; }
    public function icon(): string { return 'fa-building'; }

    public function options(): Collection
    {
        return Department::orderBy('name')->get(['id', 'name'])
            ->map(fn ($d) => ['value' => (string) $d->id, 'label' => $d->name]);
    }

    public function valueLabel(string $value): ?string
    {
        return optional(Department::find($value))->name;
    }

    public function userValues(User $user): array
    {
        return $user->department_id ? [(string) $user->department_id] : [];
    }

    public function membersQuery(string $value): Builder
    {
        return User::query()->where('is_approve', true)->where('department_id', $value);
    }
}
