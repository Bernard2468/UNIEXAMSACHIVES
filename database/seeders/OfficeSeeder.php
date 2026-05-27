<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

/**
 * Seeds the well-known offices that the PWA and PR forms route through.
 *
 * - Idempotent (uses updateOrCreate) so it is safe to re-run after deploys.
 * - Adding a new office? Just append to the $offices array below.
 */
class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        // NOTE: The legacy "dean-hod" office has been retired — Director / Dean / HOD
        // forms now route through the leadership pool (positions tagged with a
        // forms category in the Positions admin) instead of a single office.
        // We intentionally do NOT delete any existing dean-hod office record so
        // historical submissions keep their references intact.

        $offices = [
            [
                'slug' => 'finance-office',
                'name' => 'Finance Office',
                'description' => 'Handles expense, cost-centre and budget item codes; Accountant and Director of Finance sign here.',
            ],
            [
                'slug' => 'internal-audit',
                'name' => 'Internal Audit',
                'description' => 'Vets requisitions and purchase authorisations.',
            ],
            [
                'slug' => 'registrar',
                'name' => 'Registrar',
                'description' => 'Final approving office. May refer requests to the Vice-Chancellor.',
            ],
            [
                'slug' => 'vc',
                'name' => "Vice-Chancellor's Office",
                'description' => 'Approves requests escalated by the Registrar.',
            ],
            [
                'slug' => 'procurement-committee',
                'name' => 'Procurement Committee',
                'description' => 'Reviews and decides on purchases and works authorisations.',
            ],
            [
                'slug' => 'director-of-finance',
                'name' => 'Director of Finance',
                'description' => 'Issues final payment authorisation to either the Accountant or the Cashier.',
            ],
            [
                'slug' => 'accountant',
                'name' => 'Accountant',
                'description' => 'Processes payments authorised to the Accountant.',
            ],
            [
                'slug' => 'cashier',
                'name' => 'Cashier',
                'description' => 'Processes payments authorised to the Cashier.',
            ],
        ];

        foreach ($offices as $office) {
            Office::updateOrCreate(
                ['slug' => $office['slug']],
                [
                    'name'        => $office['name'],
                    'description' => $office['description'],
                    'is_active'   => true,
                ]
            );
        }
    }
}
