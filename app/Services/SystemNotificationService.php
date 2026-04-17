<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\SystemSubscription;
use App\Models\PaymentTransaction;
use App\Models\SystemMaintenanceLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SystemNotificationService
{
    /**
     * Send renewal reminder notification
     */
    public function sendRenewalReminder(SystemSubscription $subscription, int $daysRemaining): void
    {
        $notification = SystemNotification::create([
            'notification_type' => 'renewal_reminder',
            'target_audience' => 'admins_only',
            'subscription_id' => $subscription->id,
            'priority' => $daysRemaining <= 7 ? 'high' : 'medium',
            'title' => "Subscription Renewal Reminder - {$daysRemaining} " . ($daysRemaining === 1 ? 'Day' : 'Days') . " Remaining",
            'message' => "Your subscription for {$subscription->institution_name} will expire in {$daysRemaining} " . 
                         ($daysRemaining === 1 ? 'day' : 'days') . ". Please renew to avoid service interruption.",
            'short_message' => "Subscription expiring in {$daysRemaining} " . ($daysRemaining === 1 ? 'day' : 'days'),
            'icon' => 'icofont-warning-alt',
            'color' => $daysRemaining <= 7 ? 'warning' : 'info',
            'action_buttons' => [
                [
                    'label' => 'Renew Now',
                    'url' => route('super-admin.subscriptions.renew', $subscription->id),
                    'style' => 'primary'
                ],
                [
                    'label' => 'View Details',
                    'url' => route('super-admin.subscriptions.show', $subscription->id),
                    'style' => 'secondary'
                ]
            ],
            'display_as_banner' => $daysRemaining <= 7,
            'display_in_notification_center' => true,
            'send_email' => true,
            'is_dismissible' => true,
            'display_from' => now(),
            'display_until' => $subscription->subscription_end_date,
            'is_active' => true,
            'created_by' => 1, // System
            'is_automated' => true,
        ]);

        // Set total recipients
        $adminUsers = User::where('role', 'admin')->orWhere('role', 'super_admin')->get();
        $notification->setTotalRecipients($adminUsers->count());

        // Send email notifications
        if ($notification->send_email) {
            $this->sendEmailNotifications($notification, $adminUsers);
        }

        Log::info('Renewal reminder sent', [
            'subscription_id' => $subscription->id,
            'days_remaining' => $daysRemaining
        ]);
    }

    /**
     * Send expiry notification
     */
    public function sendExpiryNotification(SystemSubscription $subscription): void
    {
        $notification = SystemNotification::create([
            'notification_type' => 'subscription_expired',
            'target_audience' => 'admins_only',
            'subscription_id' => $subscription->id,
            'priority' => 'critical',
            'title' => 'Subscription Expired',
            'message' => "Your subscription for {$subscription->institution_name} has expired. You have {$subscription->grace_period_days} days grace period to renew.",
            'short_message' => 'Subscription expired - Grace period active',
            'icon' => 'icofont-error',
            'color' => 'danger',
            'action_buttons' => [
                [
                    'label' => 'Renew Immediately',
                    'url' => route('super-admin.subscriptions.renew', $subscription->id),
                    'style' => 'danger'
                ]
            ],
            'display_as_banner' => true,
            'display_in_notification_center' => true,
            'send_email' => true,
            'is_dismissible' => false,
            'display_from' => now(),
            'is_active' => true,
            'created_by' => 1,
            'is_automated' => true,
        ]);

        $adminUsers = User::where('role', 'admin')->orWhere('role', 'super_admin')->get();
        $notification->setTotalRecipients($adminUsers->count());

        if ($notification->send_email) {
            $this->sendEmailNotifications($notification, $adminUsers);
        }

        Log::warning('Subscription expired notification sent', [
            'subscription_id' => $subscription->id
        ]);
    }

    /**
     * Send renewal success notification
     */
    public function sendRenewalSuccessNotification(SystemSubscription $subscription, PaymentTransaction $transaction): void
    {
        $notification = SystemNotification::create([
            'notification_type' => 'payment_success',
            'target_audience' => 'admins_only',
            'subscription_id' => $subscription->id,
            'related_transaction_id' => $transaction->id,
            'priority' => 'medium',
            'title' => 'Subscription Renewed Successfully',
            'message' => "Your subscription for {$subscription->institution_name} has been renewed successfully. New expiry date: {$subscription->subscription_end_date->format('d M, Y')}.",
            'short_message' => 'Subscription renewed successfully',
            'icon' => 'icofont-check-circled',
            'color' => 'success',
            'action_buttons' => [
                [
                    'label' => 'View Receipt',
                    'url' => route('super-admin.payments.show', $transaction->id),
                    'style' => 'primary'
                ]
            ],
            'display_as_banner' => false,
            'display_in_notification_center' => true,
            'send_email' => true,
            'is_dismissible' => true,
            'display_from' => now(),
            'display_until' => now()->addDays(7),
            'is_active' => true,
            'created_by' => 1,
            'is_automated' => true,
        ]);

        $adminUsers = User::where('role', 'admin')->orWhere('role', 'super_admin')->get();
        $notification->setTotalRecipients($adminUsers->count());

        if ($notification->send_email) {
            $this->sendEmailNotifications($notification, $adminUsers);
        }

        Log::info('Renewal success notification sent', [
            'subscription_id' => $subscription->id,
            'transaction_id' => $transaction->id
        ]);
    }

    /**
     * Send auto-renewal failed notification
     */
    public function sendAutoRenewalFailedNotification(SystemSubscription $subscription, string $reason): void
    {
        $notification = SystemNotification::create([
            'notification_type' => 'payment_failed',
            'target_audience' => 'admins_only',
            'subscription_id' => $subscription->id,
            'priority' => 'high',
            'title' => 'Auto-Renewal Failed',
            'message' => "Automatic renewal for {$subscription->institution_name} failed. Reason: {$reason}. Please renew manually to avoid service disruption.",
            'short_message' => 'Auto-renewal failed - Manual action required',
            'icon' => 'icofont-warning',
            'color' => 'danger',
            'action_buttons' => [
                [
                    'label' => 'Renew Manually',
                    'url' => route('super-admin.subscriptions.renew', $subscription->id),
                    'style' => 'danger'
                ]
            ],
            'display_as_banner' => true,
            'display_in_notification_center' => true,
            'send_email' => true,
            'is_dismissible' => true,
            'display_from' => now(),
            'is_active' => true,
            'created_by' => 1,
            'is_automated' => true,
        ]);

        $adminUsers = User::where('role', 'admin')->orWhere('role', 'super_admin')->get();
        $notification->setTotalRecipients($adminUsers->count());

        if ($notification->send_email) {
            $this->sendEmailNotifications($notification, $adminUsers);
        }

        Log::warning('Auto-renewal failed notification sent', [
            'subscription_id' => $subscription->id,
            'reason' => $reason
        ]);
    }

    /**
     * Send suspension notification
     */
    public function sendSuspensionNotification(SystemSubscription $subscription): void
    {
        $notification = SystemNotification::create([
            'notification_type' => 'subscription_suspended',
            'target_audience' => 'all_users',
            'subscription_id' => $subscription->id,
            'priority' => 'critical',
            'title' => 'System Suspended - Payment Required',
            'message' => "Your subscription has been suspended due to non-payment. Please contact your administrator to renew immediately.",
            'short_message' => 'System suspended - Payment required',
            'icon' => 'icofont-ban',
            'color' => 'danger',
            'display_as_banner' => true,
            'display_in_notification_center' => true,
            'send_email' => true,
            'is_dismissible' => false,
            'requires_acknowledgment' => true,
            'display_from' => now(),
            'is_active' => true,
            'created_by' => 1,
            'is_automated' => true,
        ]);

        $allUsers = User::all();
        $notification->setTotalRecipients($allUsers->count());

        if ($notification->send_email) {
            $this->sendEmailNotifications($notification, $allUsers);
        }

        Log::critical('Suspension notification sent', [
            'subscription_id' => $subscription->id
        ]);
    }

    /**
     * Send maintenance scheduled notification
     */
    public function sendMaintenanceScheduledNotification(SystemMaintenanceLog $maintenance): void
    {
        $notification = SystemNotification::create([
            'notification_type' => 'maintenance_scheduled',
            'target_audience' => 'all_users',
            'related_maintenance_id' => $maintenance->id,
            'priority' => $maintenance->impact_level === 'critical' ? 'high' : 'medium',
            'title' => 'Scheduled Maintenance - ' . $maintenance->title,
            'message' => $maintenance->description . "\n\nScheduled: " . 
                        $maintenance->scheduled_start->format('d M, Y H:i') . ' - ' . 
                        $maintenance->scheduled_end->format('H:i'),
            'short_message' => 'Maintenance scheduled on ' . $maintenance->scheduled_start->format('d M, Y'),
            'icon' => 'icofont-tools',
            'color' => $maintenance->requires_downtime ? 'warning' : 'info',
            'display_as_banner' => $maintenance->display_banner,
            'display_in_notification_center' => true,
            'send_email' => true,
            'is_dismissible' => true,
            'display_from' => $maintenance->banner_display_from ?? now(),
            'display_until' => $maintenance->scheduled_end,
            'is_active' => true,
            'created_by' => $maintenance->performed_by,
            'is_automated' => false,
        ]);

        $allUsers = User::where('is_approve', true)->get();
        $notification->setTotalRecipients($allUsers->count());

        if ($notification->send_email) {
            $this->sendEmailNotifications($notification, $allUsers);
        }

        $maintenance->notifyUsers('scheduled');
        $maintenance->incrementNotifiedUsersCount($allUsers->count());

        Log::info('Maintenance scheduled notification sent', [
            'maintenance_id' => $maintenance->id
        ]);
    }

    /**
     * Send maintenance started notification
     */
    public function sendMaintenanceStartedNotification(SystemMaintenanceLog $maintenance): void
    {
        $notification = SystemNotification::create([
            'notification_type' => 'maintenance_started',
            'target_audience' => 'all_users',
            'related_maintenance_id' => $maintenance->id,
            'priority' => 'high',
            'title' => 'Maintenance In Progress - ' . $maintenance->title,
            'message' => $maintenance->description . ($maintenance->requires_downtime ? "\n\nSome features may be unavailable during this time." : ''),
            'short_message' => 'Maintenance in progress',
            'icon' => 'icofont-ui-settings',
            'color' => 'warning',
            'display_as_banner' => true,
            'display_in_notification_center' => true,
            'send_email' => false,
            'is_dismissible' => false,
            'display_from' => now(),
            'display_until' => $maintenance->scheduled_end,
            'is_active' => true,
            'created_by' => $maintenance->performed_by,
            'is_automated' => true,
        ]);

        $allUsers = User::where('is_approve', true)->get();
        $notification->setTotalRecipients($allUsers->count());

        Log::info('Maintenance started notification sent', [
            'maintenance_id' => $maintenance->id
        ]);
    }

    /**
     * Send maintenance completed notification
     */
    public function sendMaintenanceCompletedNotification(SystemMaintenanceLog $maintenance): void
    {
        $notification = SystemNotification::create([
            'notification_type' => 'maintenance_completed',
            'target_audience' => 'all_users',
            'related_maintenance_id' => $maintenance->id,
            'priority' => 'low',
            'title' => 'Maintenance Completed - ' . $maintenance->title,
            'message' => "Maintenance has been completed successfully. All services are now fully operational.",
            'short_message' => 'Maintenance completed',
            'icon' => 'icofont-check-circled',
            'color' => 'success',
            'display_as_banner' => false,
            'display_in_notification_center' => true,
            'send_email' => false,
            'is_dismissible' => true,
            'display_from' => now(),
            'display_until' => now()->addHours(6),
            'is_active' => true,
            'created_by' => $maintenance->performed_by,
            'is_automated' => true,
        ]);

        $allUsers = User::where('is_approve', true)->get();
        $notification->setTotalRecipients($allUsers->count());

        $maintenance->notifyUsers('maintenance_completed');

        Log::info('Maintenance completed notification sent', [
            'maintenance_id' => $maintenance->id
        ]);
    }

    /**
     * Create custom notification
     */
    public function createCustomNotification(array $data): SystemNotification
    {
        $notification = SystemNotification::create([
            'notification_type' => $data['notification_type'] ?? 'general_info',
            'target_audience' => $data['target_audience'] ?? 'all_users',
            'custom_user_ids' => $data['custom_user_ids'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'title' => $data['title'],
            'message' => $data['message'],
            'short_message' => $data['short_message'] ?? null,
            'icon' => $data['icon'] ?? 'icofont-info-circle',
            'color' => $data['color'] ?? 'info',
            'action_buttons' => $data['action_buttons'] ?? null,
            'display_as_banner' => $data['display_as_banner'] ?? false,
            'display_in_notification_center' => $data['display_in_notification_center'] ?? true,
            'send_email' => $data['send_email'] ?? false,
            'is_dismissible' => $data['is_dismissible'] ?? true,
            'requires_acknowledgment' => $data['requires_acknowledgment'] ?? false,
            'display_from' => $data['display_from'] ?? now(),
            'display_until' => $data['display_until'] ?? null,
            'is_active' => true,
            'created_by' => $data['created_by'] ?? auth()->id(),
            'is_automated' => false,
        ]);

        // Get targeted users and set total recipients
        $targetedUsers = $this->getTargetedUsers($notification);
        $notification->setTotalRecipients($targetedUsers->count());

        // Send email if requested
        if ($notification->send_email && $targetedUsers->count() > 0) {
            $this->sendEmailNotifications($notification, $targetedUsers);
        }

        return $notification;
    }

    // ===== Private Helper Methods =====

    private function getTargetedUsers(SystemNotification $notification)
    {
        return match($notification->target_audience) {
            'all_users' => User::where('is_approve', true)->get(),
            'admins_only' => User::whereIn('role', ['admin', 'super_admin'])->get(),
            'super_admins_only' => User::where('role', 'super_admin')->get(),
            'specific_users' => User::whereIn('id', $notification->custom_user_ids ?? [])->get(),
            default => collect([])
        };
    }

    private function sendEmailNotifications(SystemNotification $notification, $users): void
    {
        $emailsSent = 0;

        foreach ($users as $user) {
            try {
                // TODO: Create email template and send using Mail facade
                // Mail::to($user->email)->send(new SystemNotificationMail($notification));
                
                $emailsSent++;
            } catch (\Exception $e) {
                Log::error('Failed to send notification email', [
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($emailsSent > 0) {
            $notification->markEmailAsSent();
            $notification->incrementEmailSentCount($emailsSent);
        }
    }
}

