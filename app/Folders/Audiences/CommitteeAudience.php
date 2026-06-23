<?php

namespace App\Folders\Audiences;

use App\Models\Committee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Share with the members of a committee / board (committee_user pivot).
 */
class CommitteeAudience implements FolderAudience
{
    public function type(): string { return 'committee'; }
    public function label(): string { return 'Committee / Board'; }
    public function icon(): string { return 'fa-people-group'; }

    public function options(): Collection
    {
        return Committee::where('status', 'active')->orderBy('name')->get(['id', 'name'])
            ->map(fn ($c) => ['value' => (string) $c->id, 'label' => $c->name]);
    }

    public function valueLabel(string $value): ?string
    {
        return optional(Committee::find($value))->name;
    }

    public function userValues(User $user): array
    {
        return $user->activeCommittees()->pluck('committees.id')->map(fn ($id) => (string) $id)->all();
    }

    public function membersQuery(string $value): Builder
    {
        return User::query()->where('is_approve', true)
            ->whereHas('committees', fn ($q) => $q->where('committees.id', $value));
    }
}
