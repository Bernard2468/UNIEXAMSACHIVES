<?php

namespace App\Folders\Audiences;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Share with everyone attached to the department — its primary members
 * (users.department_id) AND its secondary members (department_user pivot). The
 * secondary side covers the "home department is Computer Engineering but also
 * teaches/takes a course in Nursing" case, which used to need a manual
 * per-person share. Secondary attachment is managed on the Manage Users page.
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
        return collect([$user->department_id])
            ->merge($user->secondaryDepartments->pluck('id'))
            ->filter()
            ->unique()
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    public function membersQuery(string $value): Builder
    {
        return User::query()->where('is_approve', true)
            ->where(function ($q) use ($value) {
                $q->where('department_id', $value)
                    ->orWhereIn('id', function ($sub) use ($value) {
                        $sub->select('user_id')->from('department_user')
                            ->where('department_id', $value);
                    });
            });
    }
}
