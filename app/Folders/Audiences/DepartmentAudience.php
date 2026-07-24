<?php

namespace App\Folders\Audiences;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Share with people attached to a department. A grant's audience_value can
 * target a specific membership scope:
 *
 *   "12"           → ALL members (primary + secondary) — the historical format,
 *                    so every pre-existing grant keeps meaning what it meant.
 *   "12:primary"   → only staff whose HOME department is 12 (users.department_id)
 *   "12:secondary" → only staff attached to 12 as an ADDITIONAL department
 *                    (department_user pivot; managed on the Manage Users page)
 *
 * userValues() emits every token the user satisfies, so the generic
 * value-matching in Folder (access checks + "shared with me") needs no
 * special-casing.
 */
class DepartmentAudience implements FolderAudience
{
    public const SCOPE_PRIMARY = 'primary';
    public const SCOPE_SECONDARY = 'secondary';

    public function type(): string { return 'department'; }
    public function label(): string { return 'Department'; }
    public function icon(): string { return 'fa-building'; }

    /**
     * Split a stored value into [departmentId, scope|null].
     * A bare id (no scope) means "all members".
     *
     * @return array{0:string,1:?string}
     */
    public static function parseValue(string $value): array
    {
        $parts = explode(':', $value, 2);

        return [$parts[0], $parts[1] ?? null];
    }

    /**
     * Every valid stored value for one department id: all-members, primary-only
     * and secondary-only. Used to build server-side allow-lists.
     *
     * @return array<int,string>
     */
    public static function scopedValues(string $departmentId): array
    {
        return [
            $departmentId,
            $departmentId . ':' . self::SCOPE_PRIMARY,
            $departmentId . ':' . self::SCOPE_SECONDARY,
        ];
    }

    public function options(): Collection
    {
        return Department::orderBy('name')->get(['id', 'name'])
            ->map(fn ($d) => ['value' => (string) $d->id, 'label' => $d->name]);
    }

    public function valueLabel(string $value): ?string
    {
        [$id, $scope] = self::parseValue($value);

        $name = optional(Department::find($id))->name;
        if (!$name) {
            return null;
        }

        return match ($scope) {
            self::SCOPE_PRIMARY => $name . ' — Primary members only',
            self::SCOPE_SECONDARY => $name . ' — Secondary members only',
            default => $name,
        };
    }

    public function userValues(User $user): array
    {
        $values = collect();

        if ($user->department_id) {
            $values->push((string) $user->department_id);
            $values->push($user->department_id . ':' . self::SCOPE_PRIMARY);
        }

        foreach ($user->secondaryDepartments->pluck('id') as $id) {
            $values->push((string) $id);
            $values->push($id . ':' . self::SCOPE_SECONDARY);
        }

        return $values->unique()->values()->all();
    }

    public function membersQuery(string $value): Builder
    {
        [$id, $scope] = self::parseValue($value);

        $query = User::query()->where('is_approve', true);

        if ($scope === self::SCOPE_PRIMARY) {
            return $query->where('department_id', $id);
        }

        if ($scope === self::SCOPE_SECONDARY) {
            return $query->whereIn('id', function ($sub) use ($id) {
                $sub->select('user_id')->from('department_user')
                    ->where('department_id', $id);
            });
        }

        return $query->where(function ($q) use ($id) {
            $q->where('department_id', $id)
                ->orWhereIn('id', function ($sub) use ($id) {
                    $sub->select('user_id')->from('department_user')
                        ->where('department_id', $id);
                });
        });
    }
}
