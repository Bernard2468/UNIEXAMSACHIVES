<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Mail\Message;

class ResendMailService
{
    protected $apiKey;
    protected $domain;

    public function __construct()
    {
        $this->apiKey = config('services.resend.api_key');
        $this->domain = config('services.resend.domain', 'academicdigital.space');
    }

    /**
     * Send a simple email using Resend SMTP
     */
    public function sendEmail($to, $subject, $htmlContent, $from = null, $attachments = [], $retryCount = 0, $fromName = null)
    {
        try {
            $from = $from ?: config('mail.from.address');
            $fromName = $fromName ?: (config('mail.from.name') ?: null);
            
            // Validate from address format
            if (!str_contains($from, '@')) {
                throw new Exception("Invalid from address: {$from}");
            }
            
            $messageId = Str::uuid()->toString();
            
            Mail::send([], [], function (Message $message) use ($to, $subject, $htmlContent, $from, $attachments, $fromName) {
                $message->to($to)
                        ->subject($subject)
                        ->from($from, $fromName)
                        ->html($htmlContent);
                
                // Add attachments if any
                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        if (isset($attachment['filename']) && isset($attachment['content'])) {
                            // If content is base64 encoded, decode it
                            $content = $attachment['content'];
                            if (base64_decode($content, true) !== false) {
                                $content = base64_decode($content);
                            }
                            
                            $message->attachData(
                                $content,
                                $attachment['filename'],
                                ['mime' => $attachment['type'] ?? 'application/octet-stream']
                            );
                        }
                    }
                }
            });

            return [
                'success' => true,
                'message_id' => $messageId,
                'response' => 'Email sent via SMTP'
            ];

        } catch (Exception $e) {
            Log::error('Failed to send email via SMTP', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send email using template (fallback to regular email since we're using SMTP)
     */
    public function sendTemplateEmail($to, $templateId, $templateData, $from = null)
    {
        try {
            $from = $from ?: config('mail.from.address');
            
            // Since we're using SMTP, we'll generate HTML content from template data
            $htmlContent = $this->generateTemplateContent($templateId, $templateData);
            
            $result = $this->sendEmail($to, $templateData['subject'] ?? 'Template Email', $htmlContent, $from);
            
            if ($result['success']) {
                Log::info('Template email sent successfully via SMTP', [
                    'to' => $to,
                    'template_id' => $templateId,
                    'message_id' => $result['message_id']
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Failed to send template email via SMTP', [
                'to' => $to,
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate HTML content from template data
     */
    private function generateTemplateContent($templateId, $templateData)
    {
        // Basic template generation - you can customize this based on your needs
        $content = "<html><body>";
        $content .= "<h2>" . ($templateData['title'] ?? 'Email') . "</h2>";
        $content .= "<p>" . ($templateData['message'] ?? 'No message content') . "</p>";
        
        // Add any other template data
        foreach ($templateData as $key => $value) {
            if (!in_array($key, ['title', 'message', 'subject']) && is_string($value)) {
                $content .= "<p><strong>" . ucfirst($key) . ":</strong> " . htmlspecialchars($value) . "</p>";
            }
        }
        
        $content .= "</body></html>";
        return $content;
    }

    /**
     * Send bulk emails
     */
    public function sendBulkEmails($recipients, $subject, $htmlContent, $from = null)
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $result = $this->sendEmail($recipient, $subject, $htmlContent, $from);
            $results[] = [
                'recipient' => $recipient,
                'result' => $result
            ];
            
            // Smart rate limiting for Resend (2 requests per second)
            $requestCount++;
            $elapsedTime = microtime(true) - $startTime;
            $expectedTime = $requestCount * 0.5; // 0.5 seconds per request
            
            if ($elapsedTime < $expectedTime) {
                $sleepTime = ($expectedTime - $elapsedTime) * 1000000; // Convert to microseconds
                usleep((int)$sleepTime);
            }
            
            // Ensure minimum delay between requests
            usleep(500000); // 0.5 second minimum
        }

        return $results;
    }

    /**
     * Send campaign emails with better error handling and rate limiting
     */
    public function sendCampaignEmails($campaign, $recipients, $from = null)
    {
        $results = [];
        $from = $from ?: config('mail.from.address');
        $requestCount = 0;
        $startTime = microtime(true);
        
        $totalRecipients = count($recipients);
        Log::info("Starting campaign email send to {$totalRecipients} recipients");
        
        foreach ($recipients as $index => $recipient) {
            try {
                Log::info("Processing recipient " . ($index + 1) . "/{$totalRecipients}: {$recipient->email}");
                
                // Generate HTML content for each recipient
                $htmlContent = view('mails.campaign', [
                    'campaign' => $campaign,
                    'user' => $recipient,
                    'subject' => $campaign->subject,
                    'message' => $campaign->message,
                ])->render();

                // Prepare attachments for Resend API
                $attachments = [];
                if ($campaign->attachments && is_array($campaign->attachments)) {
                    Log::info("Processing attachments for campaign {$campaign->id}", [
                        'attachments_count' => count($campaign->attachments),
                        'attachments' => $campaign->attachments
                    ]);
                    
                    foreach ($campaign->attachments as $attachment) {
                        try {
                            // Validate attachment structure
                            if (!isset($attachment['path']) || !isset($attachment['name'])) {
                                Log::warning("Invalid attachment structure", $attachment);
                                continue;
                            }
                            
                            $filePath = storage_path('app/public/' . $attachment['path']);
                            Log::info("Processing attachment file", [
                                'file_path' => $filePath,
                                'attachment_data' => $attachment
                            ]);
                            
                            if (file_exists($filePath)) {
                                $fileContent = file_get_contents($filePath);
                                if ($fileContent !== false) {
                                    $attachments[] = [
                                        'filename' => $attachment['name'],
                                        'content' => base64_encode($fileContent),
                                        'type' => $attachment['type'] ?? mime_content_type($filePath),
                                    ];
                                    
                                    Log::info("Attachment prepared successfully", [
                                        'filename' => $attachment['name'],
                                        'size' => strlen($fileContent),
                                        'type' => $attachment['type'] ?? mime_content_type($filePath)
                                    ]);
                                } else {
                                    Log::error("Failed to read file content", ['file_path' => $filePath]);
                                }
                            } else {
                                Log::error("Attachment file not found", [
                                    'file_path' => $filePath,
                                    'attachment' => $attachment
                                ]);
                            }
                        } catch (Exception $e) {
                            Log::error("Error processing attachment", [
                                'attachment' => $attachment,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } else {
                    Log::info("No attachments to process for campaign {$campaign->id}");
                }

                // Send email with unique ID for each recipient
                Log::info("Sending email to recipient", [
                    'email' => $recipient->email,
                    'attachments_count' => count($attachments),
                    'has_attachments' => !empty($attachments)
                ]);
                
                $result = $this->sendEmail(
                    $recipient->email,
                    $campaign->subject,
                    $htmlContent,
                    $from,
                    $attachments
                );

                $results[] = [
                    'recipient' => $recipient,
                    'result' => $result,
                    'email' => $recipient->email,
                    'user_id' => $recipient->id
                ];

                // Smart rate limiting for Resend (2 requests per second)
                $requestCount++;
                $elapsedTime = microtime(true) - $startTime;
                $expectedTime = $requestCount * 0.5; // 0.5 seconds per request
                
                if ($elapsedTime < $expectedTime) {
                    $sleepTime = ($expectedTime - $elapsedTime) * 1000000; // Convert to microseconds
                    usleep((int)$sleepTime);
                }
                
                // Ensure minimum delay between requests
                usleep(500000); // 0.5 second minimum

            } catch (Exception $e) {
                $results[] = [
                    'recipient' => $recipient,
                    'result' => [
                        'success' => false,
                        'error' => $e->getMessage()
                    ],
                    'email' => $recipient->email,
                    'user_id' => $recipient->id
                ];

                Log::error("Failed to send campaign email to user {$recipient->id}", [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Verify email address (simplified for restricted API keys)
     */
    public function verifyEmail($email)
    {
        try {
            // For restricted API keys, we'll just validate the email format
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => true,
                    'verified' => true,
                    'message' => 'Email format is valid'
                ];
            } else {
                return [
                    'success' => false,
                    'verified' => false,
                    'error' => 'Invalid email format'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'verified' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate a valid UUID for Resend API
     */
    private function generateResendId()
    {
        return Str::uuid()->toString();
    }

    /**
     * Validate UUID format
     */
    private function isValidUuid($uuid)
    {
        return Str::isUuid($uuid);
    }
}
