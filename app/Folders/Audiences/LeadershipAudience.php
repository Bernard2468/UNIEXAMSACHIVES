<?php

namespace App\Folders\Audiences;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Share with a leadership pool — everyone holding a Position whose category is
 * HOD / Dean / Director (the same categories Forms uses for routing).
 */
class LeadershipAudience implements FolderAudience
{
    public function type(): string { return 'leadership'; }
    public function label(): string { return 'Leadership'; }
    public function icon(): string { return 'fa-user-tie'; }

    public function options(): Collection
    {
        return collect(Position::CATEGORIES)
            ->map(fn ($label, $key) => ['value' => $key, 'label' => $label])
            ->values();
    }

    public function valueLabel(string $value): ?string
    {
        return Position::CATEGORIES[$value] ?? null;
    }

    public function userValues(User $user): array
    {
        $category = optional($user->position)->category;

        return $category ? [(string) $category] : [];
    }

    public function membersQuery(string $value): Builder
    {
        return User::query()->where('is_approve', true)
            ->whereHas('position', fn ($q) => $q->where('category', $value));
    }
}
