<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            [
                'key'              => 'restrict_email_domain',
                'value'            => '0',
                'category'         => 'security',
                'label'            => 'Restrict to Institutional Email',
                'description'      => 'When enabled, only emails from the configured domain can register or log in to the system.',
                'data_type'        => 'boolean',
                'is_public'        => false,
                'is_editable'      => true,
                'requires_restart' => false,
                'validation_rules' => 'boolean',
                'default_value'    => '0',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'key'              => 'allowed_email_domain',
                'value'            => 'cug.edu.gh',
                'category'         => 'security',
                'label'            => 'Allowed Email Domain',
                'description'      => 'Institutional domain to allow (e.g. cug.edu.gh — without the @ symbol). Only enforced when the restriction toggle above is enabled.',
                'data_type'        => 'string',
                'is_public'        => false,
                'is_editable'      => true,
                'requires_restart' => false,
                'validation_rules' => 'nullable|string|max:255',
                'default_value'    => 'cug.edu.gh',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->whereIn('key', ['restrict_email_domain', 'allowed_email_domain'])
            ->delete();
    }
};
