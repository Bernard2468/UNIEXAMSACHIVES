<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetSuperAdminPassword extends Command
{
    /**
     * The console command name and signature.
     */
    protected $signature = 'super-admin:reset-password
                            {--email=metascholarlimited@gmail.com : Email address of the super admin account}
                            {--password= : Provide a specific temporary password}
                            {--force-create : Create the account if it does not exist without prompting}';

    /**
     * The console command description.
     */
    protected $description = 'Reset or recreate the primary super admin credentials so you can regain access';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = strtolower($this->option('email'));
        $password = $this->option('password') ?: $this->generateSecurePassword();

        if (!$this->option('password')) {
            $this->info('No password supplied. Generated a secure temporary password.');
        }

        $superAdmin = User::where('email', $email)->first();

        if (!$superAdmin) {
            if (
                !$this->option('force-create')
                && !$this->confirm("No super admin account found for {$email}. Create it now?")
            ) {
                $this->warn('No changes were made.');
                return Command::INVALID;
            }

            $superAdmin = User::create([
                'email' => $email,
                'first_name' => 'Meta',
                'last_name' => 'Scholar',
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_admin' => true,
                'is_approve' => true,
                'password_changed' => false,
                'super_admin_access_granted_at' => now(),
                'super_admin_granted_by' => null,
            ]);

            $this->info('Super admin account created.');
        } else {
            $superAdmin->forceFill([
                'password' => Hash::make($password),
                'password_changed' => false,
                'super_admin_access_granted_at' => now(),
            ])->save();

            $this->info('Super admin password reset successfully.');
        }

        $this->line('');
        $this->table(
            ['Credential', 'Value'],
            [
                ['Email', $email],
                ['Temporary Password', $password],
            ]
        );
        $this->warn('Change this password immediately after logging in.');

        return Command::SUCCESS;
    }

    /**
     * Generate a reasonably strong temporary password.
     */
    private function generateSecurePassword(): string
    {
        $segments = [
            Str::random(6),
            Str::upper(Str::random(4)),
            Str::padLeft((string) random_int(100, 9999), 4, '0'),
        ];

        $symbols = ['!', '@', '#', '$', '%', '&', '*'];

        return implode('', $segments) . $symbols[array_rand($symbols)];
    }
}

