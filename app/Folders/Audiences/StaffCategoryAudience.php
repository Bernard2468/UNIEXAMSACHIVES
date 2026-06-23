<?php

namespace App\Folders\Audiences;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Share with a staff category (Junior Staff, Senior Staff, …). The category
 * values are the same fixed set used by registration and the communication
 * module, so the picker stays in lock-step with the rest of the system.
 */
class StaffCategoryAudience implements FolderAudience
{
    /** Must match the values stored in users.staff_category. */
    public const CATEGORIES = [
        'Junior Staff',
        'Senior Staff',
        'Senior Member (Non-Teaching)',
        'Senior Member (Teaching)',
    ];

    public function type(): string { return 'staff_category'; }
    public function label(): string { return 'Staff category'; }
    public function icon(): string { return 'fa-user-tag'; }

    public function options(): Collection
    {
        return collect(self::CATEGORIES)->map(fn ($c) => ['value' => $c, 'label' => $c]);
    }

    public function valueLabel(string $value): ?string
    {
        return in_array($value, self::CATEGORIES, true) ? $value : null;
    }

    public function userValues(User $user): array
    {
        return $user->staff_category ? [(string) $user->staff_category] : [];
    }

    public function membersQuery(string $value): Builder
    {
        return User::query()->where('is_approve', true)->where('staff_category', $value);
    }
}
