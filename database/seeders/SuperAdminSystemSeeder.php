<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\SystemSubscription;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SuperAdminSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Setting up Super Admin System...');

        // 1. Create first Super Admin user
        $this->createSuperAdmin();

        // 2. Create system settings
        $this->createSystemSettings();

        // 3. Create initial subscription (optional)
        $this->createInitialSubscription();

        $this->command->info('✅ Super Admin System setup completed!');
    }

    /**
     * Create the first super admin user
     */
    private function createSuperAdmin(): void
    {
        $this->command->info('Creating Super Admin user...');

        $superAdmin = User::firstOrCreate(
            ['email' => 'metascholarlimited@gmail.com'],
            [
                'first_name' => 'Meta',
                'last_name' => 'Scholar',
                'password' => Hash::make('SuperAdmin@2025'), // CHANGE THIS PASSWORD IMMEDIATELY AFTER FIRST LOGIN
                'role' => 'super_admin',
                'is_admin' => true,
                'is_approve' => true,
                'password_changed' => false, // Will be prompted to change
                'super_admin_access_granted_at' => now(),
                'super_admin_granted_by' => null, // System granted
            ]
        );

        $this->command->warn('⚠️  IMPORTANT: Super Admin Credentials');
        $this->command->info('Email: metascholarlimited@gmail.com');
        $this->command->info('Password: SuperAdmin@2025');
        $this->command->warn('⚠️  CHANGE THIS PASSWORD IMMEDIATELY AFTER FIRST LOGIN!');
    }

    /**
     * Create system settings
     */
    private function createSystemSettings(): void
    {
        $this->command->info('Creating system settings...');

        $settings = [
            // Payment Gateway Settings
            [
                'key' => 'paystack_public_key',
                'value' => '',
                'category' => 'payment_gateway',
                'label' => 'Paystack Public Key',
                'description' => 'Your Paystack public API key (starts with pk_)',
                'data_type' => 'string',
                'is_public' => false,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'nullable|string',
                'default_value' => '',
            ],
            [
                'key' => 'paystack_secret_key',
                'value' => '',
                'category' => 'payment_gateway',
                'label' => 'Paystack Secret Key',
                'description' => 'Your Paystack secret API key (starts with sk_)',
                'data_type' => 'string',
                'is_public' => false,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'nullable|string',
                'default_value' => '',
            ],
            [
                'key' => 'paystack_webhook_secret',
                'value' => '',
                'category' => 'payment_gateway',
                'label' => 'Paystack Webhook Secret',
                'description' => 'Your Paystack webhook secret for signature validation',
                'data_type' => 'string',
                'is_public' => false,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'nullable|string',
                'default_value' => '',
            ],

            // Subscription Settings
            [
                'key' => 'subscription_base_price',
                'value' => '5000.00',
                'category' => 'subscription',
                'label' => 'Annual Subscription Price',
                'description' => 'Base subscription price per year (in default currency). Schools can subscribe for 1, 2, or 3 years.',
                'data_type' => 'string',
                'is_public' => true,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'required|numeric|min:0',
                'default_value' => '5000.00',
            ],
            [
                'key' => 'subscription_grace_period_days',
                'value' => '7',
                'category' => 'subscription',
                'label' => 'Grace Period (Days)',
                'description' => 'Number of days after expiry before subscription is suspended',
                'data_type' => 'integer',
                'is_public' => true,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'required|integer|min:0|max:30',
                'default_value' => '7',
            ],
            [
                'key' => 'auto_renewal_enabled',
                'value' => '1',
                'category' => 'subscription',
                'label' => 'Enable Auto-Renewal',
                'description' => 'Allow automatic subscription renewals using saved payment methods',
                'data_type' => 'boolean',
                'is_public' => true,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'boolean',
                'default_value' => '1',
            ],
            [
                'key' => 'renewal_reminder_days',
                'value' => json_encode([30, 14, 7, 1]),
                'category' => 'subscription',
                'label' => 'Renewal Reminder Days',
                'description' => 'Enter 4 numbers (days) before expiry to send renewal reminders. Example: 30, 14, 7, 1',
                'data_type' => 'json',
                'is_public' => false,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'json',
                'default_value' => json_encode([30, 14, 7, 1]),
            ],

            // General Settings
            [
                'key' => 'system_name',
                'value' => 'University Exam Archives',
                'category' => 'general',
                'label' => 'System Name',
                'description' => 'The name of your system/institution',
                'data_type' => 'string',
                'is_public' => true,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'required|string|max:255',
                'default_value' => 'University Exam Archives',
            ],
            [
                'key' => 'system_email',
                'value' => 'admin@institution.edu',
                'category' => 'general',
                'label' => 'System Email',
                'description' => 'Primary email address for system notifications',
                'data_type' => 'string',
                'is_public' => true,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'required|email',
                'default_value' => 'admin@institution.edu',
            ],

            // Email Settings
            [
                'key' => 'send_renewal_emails',
                'value' => '1',
                'category' => 'email',
                'label' => 'Send Renewal Emails',
                'description' => 'Automatically send email notifications for subscription renewals',
                'data_type' => 'boolean',
                'is_public' => false,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'boolean',
                'default_value' => '1',
            ],
            [
                'key' => 'send_payment_receipts',
                'value' => '1',
                'category' => 'email',
                'label' => 'Send Payment Receipts',
                'description' => 'Automatically send payment receipts via email',
                'data_type' => 'boolean',
                'is_public' => false,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'boolean',
                'default_value' => '1',
            ],

            // Currency Settings
            [
                'key' => 'default_currency',
                'value' => 'GHS',
                'category' => 'general',
                'label' => 'Default Currency',
                'description' => 'Default currency for subscriptions and payments',
                'data_type' => 'string',
                'is_public' => true,
                'is_editable' => true,
                'requires_restart' => false,
                'validation_rules' => 'required|string|size:3',
                'default_value' => 'GHS',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✓ System settings created');
    }

    /**
     * Create initial subscription (optional - for immediate testing)
     */
    private function createInitialSubscription(): void
    {
        $this->command->info('Creating initial subscription...');

        // Get super admin
        $superAdmin = User::where('role', 'super_admin')->first();

        if (!$superAdmin) {
            $this->command->warn('Super admin not found, skipping subscription creation');
            return;
        }

        // Create a 1-year subscription
        $subscription = SystemSubscription::firstOrCreate(
            ['institution_code' => 'metascholar-main'],
            [
                'institution_name' => 'Metascholar Consult Ltd',
                'subscription_plan' => 'enterprise',
                'subscription_start_date' => now(),
                'subscription_end_date' => now()->addYear(),
                'renewal_cycle' => 'annual',
                'renewal_amount' => 5000.00,
                'currency' => 'GHS',
                'hosting_package_type' => 'Premium Hosting',
                'package_features' => json_encode([
                    'Unlimited Users',
                    'Unlimited Storage',
                    'Priority Support',
                    'Custom Domain',
                    'Advanced Analytics',
                ]),
                'status' => 'active',
                'auto_renewal' => true,
                'grace_period_days' => 7,
                'created_by' => $superAdmin->id,
                'admin_notes' => 'Initial subscription created by seeder',
            ]
        );

        $this->command->info('✓ Initial subscription created');
        $this->command->info("  Institution: {$subscription->institution_name}");
        $this->command->info("  Expires: {$subscription->subscription_end_date->format('Y-m-d')}");
    }
}

