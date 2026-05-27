<?php

namespace App\Forms;

use InvalidArgumentException;

/**
 * Holds every available FormDefinition in the system.
 *
 * Bound as a singleton in AppServiceProvider so the same registry is reused
 * across the request lifecycle. To add a new form, instantiate it once in
 * AppServiceProvider::register() and call $registry->register($definition).
 */
class FormRegistry
{
    /** @var array<string, BaseFormDefinition> */
    protected array $definitions = [];

    public function register(BaseFormDefinition $definition): void
    {
        $slug = $definition->slug();
        if (isset($this->definitions[$slug])) {
            throw new InvalidArgumentException("Form with slug [{$slug}] already registered.");
        }
        $this->definitions[$slug] = $definition;
    }

    public function find(string $slug): ?BaseFormDefinition
    {
        return $this->definitions[$slug] ?? null;
    }

    public function findOrFail(string $slug): BaseFormDefinition
    {
        $definition = $this->find($slug);
        if (!$definition) {
            throw new InvalidArgumentException("Unknown form slug [{$slug}].");
        }
        return $definition;
    }

    /**
     * @return array<int, BaseFormDefinition>
     */
    public function all(): array
    {
        return array_values($this->definitions);
    }

    /**
     * Slugs of every registered form — useful for validation rules and tests.
     */
    public function slugs(): array
    {
        return array_keys($this->definitions);
    }
}
