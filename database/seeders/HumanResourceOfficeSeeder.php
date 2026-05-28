<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

/**
 * Seeds the Human Resource Unit office used by the Leave Resumption form.
 *
 * Intentionally lives in its own seeder (instead of being appended to
 * OfficeSeeder) so that re-running it does not touch the descriptions or
 * other fields of unrelated offices (Finance, Registrar, etc.) which may
 * have been edited by an admin in the UI.
 *
 * Idempotent: uses updateOrCreate(slug=…), safe to re-run on a populated DB.
 *
 * Usage:
 *   php artisan db:seed --class=HumanResourceOfficeSeeder
 */
class HumanResourceOfficeSeeder extends Seeder
{
    public function run(): void
    {
        Office::updateOrCreate(
            ['slug' => 'human-resource-unit'],
            [
                'name'        => 'Human Resource Unit',
                'description' => 'Vets leave resumption dates and outstanding leave-day balances for further action. Used by the Leave Resumption form.',
                'is_active'   => true,
            ]
        );
    }
}
