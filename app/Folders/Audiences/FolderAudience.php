<?php

namespace App\Folders\Audiences;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * A "group" a folder can be shared with (a Department, a staff category, a
 * Committee, an Office, the leadership pool, everyone, …).
 *
 * Membership is resolved LIVE — never snapshotted — so a folder shared with a
 * group automatically follows people in and out of that group.
 *
 * Adding a new shareable group later is just a new class implementing this
 * contract, registered in {@see \App\Providers\AppServiceProvider::register()}.
 * No migration, no change to the access-check or "shared with me" logic.
 */
interface FolderAudience
{
    /**
     * Stable machine key stored in folder_grants.audience_type
     * (e.g. 'department'). Never change once data exists.
     */
    public function type(): string;

    /** Human label for the group TYPE (e.g. "Department"). */
    public function label(): string;

    /** Font Awesome icon class for the picker (e.g. 'fa-building'). */
    public function icon(): string;

    /**
     * Choices the owner can pick from, each: ['value' => string, 'label' => string].
     * For value-less audiences (e.g. "everyone") return a single ['value' => ''] row.
     */
    public function options(): Collection;

    /** Friendly label for one stored value (e.g. department id -> "Computer Science"). */
    public function valueLabel(string $value): ?string;

    /**
     * The values of THIS audience type the given user belongs to.
     * Used both for access checks and for the "shared with me" listing, so the
     * matching rule lives in exactly one place. Return [] if the user is in no
     * group of this type. "Everyone" returns [''] for every user.
     *
     * @return array<int,string>
     */
    public function userValues(User $user): array;

    /**
     * Approved users who are members of the group `value` — used to send the
     * one-time "shared with you" notification at grant time.
     */
    public function membersQuery(string $value): Builder;
}
