<?php

namespace App\Folders\Audiences;

use App\Models\User;

/**
 * Holds the set of group types a folder can be shared with. Mirrors the Forms
 * FormRegistry pattern — bound as a singleton in AppServiceProvider and the
 * single place new group types are wired in.
 */
class FolderAudienceRegistry
{
    /** @var array<string,FolderAudience> */
    protected array $audiences = [];

    public function register(FolderAudience $audience): self
    {
        $this->audiences[$audience->type()] = $audience;

        return $this;
    }

    public function get(string $type): ?FolderAudience
    {
        return $this->audiences[$type] ?? null;
    }

    public function has(string $type): bool
    {
        return isset($this->audiences[$type]);
    }

    /** @return array<string,FolderAudience> */
    public function all(): array
    {
        return $this->audiences;
    }

    /**
     * Every (type, value) pair the user belongs to, as a flat list of
     * ['type' => ..., 'values' => [...]] entries with non-empty value sets.
     * This is the live "what groups am I in?" map used by the access check and
     * the "shared with me" query, computed once per request.
     *
     * @return array<int,array{type:string,values:array<int,string>}>
     */
    public function membershipFor(User $user): array
    {
        $out = [];
        foreach ($this->audiences as $audience) {
            $values = $audience->userValues($user);
            if (!empty($values)) {
                $out[] = ['type' => $audience->type(), 'values' => array_values(array_map('strval', $values))];
            }
        }

        return $out;
    }
}
