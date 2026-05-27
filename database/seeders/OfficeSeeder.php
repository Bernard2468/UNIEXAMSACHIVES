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
        $offices = [
            [
                'slug' => 'dean-hod',
                'name' => 'Director / Dean / HOD',
                'description' => 'Director, Dean or Head of Department who co-signs the requisitioner section.',
            ],
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
