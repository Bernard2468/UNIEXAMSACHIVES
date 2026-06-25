<?php

namespace App\Services;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class CampaignDeliveryService
{
    /**
     * Deliver a scheduled campaign that is now due.
     */
    public function deliver(EmailCampaign $campaign): void
    {
        // Through-routed memo: deliver to the intermediary only; the To/Cc lists
        // wait until they forward it (identical to the manual send path).
        if ($campaign->through_user_id && $campaign->through_status === 'pending') {
            $throughUser = User::where('is_approve', true)->find($campaign->through_user_id);
            if ($throughUser) {
                $this->deliverToThrough($campaign, $throughUser);
                return;
            }
        }

        $this->deliverToRecipients($campaign);
    }

    /**
     * Send to the To and Cc recipient rows prepared at compose time.
     */
    private function deliverToRecipients(EmailCampaign $campaign): void
    {
        // Claim the campaign so an overlapping run / the scheduledForSending scope
        // cannot pick it up again while we work.
        $campaign->update(['status' => 'sending']);

        $resendService = new ResendMailService();
        $sentCount = 0;
        $failedCount = 0;

        // Primary (To) recipients — plain subject.
        $toRecipients = $campaign->recipients()
            ->where('recipient_role', 'to')
            ->where('status', 'pending')
            ->with('user')
            ->get();

        foreach ($toRecipients as $recipient) {
            $user = $recipient->user;
            if (!$user) {
                continue;
            }

            if (!$user->is_approve) {
                $recipient->update(['status' => 'failed', 'error_message' => 'User account not approved']);
                $failedCount++;
                continue;
            }

            $result = $this->sendOne($campaign, $resendService, $user, $campaign->subject);

            if ($result['success']) {
                $recipient->update(['status' => 'sent', 'sent_at' => now()]);
                $sentCount++;
            } else {
                $recipient->update([
                    'status' => 'failed',
                    'error_message' => 'ResendMailService error: ' . ($result['error'] ?? 'Unknown error'),
                ]);
                $failedCount++;
            }

            usleep(500000); // 0.5s — respect Resend rate limit
        }

        // Cc recipients — "[Cc]" prefixed subject.
        $ccRecipients = $campaign->recipients()
            ->where('recipient_role', 'cc')
            ->where('status', 'pending')
            ->with('user')
            ->get();

        foreach ($ccRecipients as $recipient) {
            $user = $recipient->user;
            if (!$user) {
                continue;
            }

            $result = $this->sendOne($campaign, $resendService, $user, '[Cc] ' . $campaign->subject);

            $recipient->update([
                'status' => $result['success'] ? 'sent' : 'failed',
                'sent_at' => $result['success'] ? now() : null,
                'error_message' => $result['success'] ? null : ('ResendMailService error: ' . ($result['error'] ?? 'Unknown error')),
            ]);

            usleep(500000);
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);

        Log::info("Scheduled campaign {$campaign->id} delivered. Sent: {$sentCount}, Failed: {$failedCount}");
    }

    /**
     * Deliver a Through-routed scheduled memo to its intermediary only.
     * Mirror of AdvanceCommunicationController::deliverToThrough(), but uses the
     * campaign creator as the actor since there is no authenticated user.
     */
    private function deliverToThrough(EmailCampaign $campaign, User $throughUser): void
    {
        EmailCampaignRecipient::firstOrCreate([
            'comm_campaign_id' => $campaign->id,
            'user_id' => $throughUser->id,
            'recipient_role' => 'through',
        ], [
            'status' => 'pending',
            'is_active_participant' => true,
            'assigned_at' => now(),
            'last_activity_at' => now(),
        ]);

        $resendService = new ResendMailService();
        $subject = '[Through – Action Required] ' . $campaign->subject;
        $sent = 0;
        $failed = 0;

        $result = $this->sendOne($campaign, $resendService, $throughUser, $subject);

        EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
            ->where('user_id', $throughUser->id)
            ->where('recipient_role', 'through')
            ->update([
                'status' => $result['success'] ? 'sent' : 'failed',
                'sent_at' => $result['success'] ? now() : null,
                'error_message' => $result['success'] ? null : ('ResendMailService error: ' . ($result['error'] ?? 'Unknown error')),
            ]);

        $result['success'] ? $sent++ : $failed++;

        $campaign->addToWorkflowHistory('through_pending', $campaign->created_by, $throughUser->id);

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sent,
            'failed_count' => $failed,
            'total_recipients' => is_array($campaign->selected_users) ? count($campaign->selected_users) : 0,
        ]);
    }

    /**
     * Render and send one campaign email. Returns ['success' => bool, 'error' => ?string].
     */
    private function sendOne(EmailCampaign $campaign, ResendMailService $resendService, User $user, string $subject): array
    {
        try {
            $htmlContent = view('mails.campaign_simple', [
                'campaign' => $campaign,
                'user' => $user,
                'subject' => $subject,
                'message' => $campaign->message,
            ])->render();

            $fromName = $campaign->creator
                ? trim($campaign->creator->first_name . ' ' . $campaign->creator->last_name)
                : (config('mail.from.name') ?: 'CUG');

            return $resendService->sendEmail(
                $user->email,
                $subject,
                $htmlContent,
                config('mail.from.address'),
                $this->buildAttachments($campaign),
                0,
                $fromName
            );
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Build the Resend attachment payload from the campaign's stored files.
     */
    private function buildAttachments(EmailCampaign $campaign): array
    {
        $attachments = [];

        if ($campaign->attachments && is_array($campaign->attachments)) {
            foreach ($campaign->attachments as $attachment) {
                $filePath = storage_path('app/public/' . $attachment['path']);
                if (file_exists($filePath)) {
                    $fileContent = file_get_contents($filePath);
                    if ($fileContent !== false) {
                        $attachments[] = [
                            'filename' => $attachment['name'],
                            'content' => base64_encode($fileContent),
                            'type' => $attachment['type'] ?? mime_content_type($filePath),
                        ];
                    }
                }
            }
        }

        return $attachments;
    }
}
