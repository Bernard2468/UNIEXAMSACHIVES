<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignEmail;
use App\Mail\CampaignEmail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\User;
use App\Models\Committee;
use App\Services\ResendMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class AdvanceCommunicationController extends Controller
{
    private function checkAdminAccess()
    {
        // Inverted: only non-admin users are allowed to access communication pages
        if (!auth()->check() || auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAdminAccess();
        
        // Get filter parameter
        $statusFilter = $request->get('status', 'all');
        $search = $request->get('search');
        
        // Build query based on filter - ONLY show campaigns created by current user
        $query = EmailCampaign::with(['creator', 'recipients'])
            ->where('created_by', auth()->id())
            ->recentFirst();
        
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        $campaigns = $query->paginate(10)->appends($request->only('status', 'search'));

        // Statistics for current user only
        $totalCampaigns = EmailCampaign::where('created_by', auth()->id())->count();
        $sentCampaigns = EmailCampaign::where('created_by', auth()->id())->byStatus('sent')->count();
        $draftCampaigns = EmailCampaign::where('created_by', auth()->id())->byStatus('draft')->count();
        $scheduledCampaigns = EmailCampaign::where('created_by', auth()->id())->byStatus('scheduled')->count();
        $sendingCampaigns = EmailCampaign::where('created_by', auth()->id())->byStatus('sending')->count();
        $failedCampaigns = EmailCampaign::where('created_by', auth()->id())->byStatus('failed')->count();
        $totalUsers = User::where('is_approve', true)->count();

        return view('admin.communication.index', compact(
            'campaigns', 
            'totalCampaigns', 
            'sentCampaigns', 
            'draftCampaigns',
            'scheduledCampaigns',
            'sendingCampaigns',
            'failedCampaigns',
            'totalUsers',
            'statusFilter'
        ));
    }

    public function create()
    {
        $this->checkAdminAccess();
        
        $users = User::where('is_approve', true)
                    ->with('position')
                    ->select('id', 'first_name', 'last_name', 'email', 'position_id')
                    ->orderBy('first_name')
                    ->get();

        // Calculate staff category counts
        $staffCategoryCounts = [
            'junior_staff' => User::where('is_approve', true)
                                ->where('staff_category', 'Junior Staff')
                                ->count(),
            'senior_staff' => User::where('is_approve', true)
                                ->where('staff_category', 'Senior Staff')
                                ->count(),
            'senior_member_non_teaching' => User::where('is_approve', true)
                                                ->where('staff_category', 'Senior Member (Non-Teaching)')
                                                ->count(),
            'senior_member_teaching' => User::where('is_approve', true)
                                            ->where('staff_category', 'Senior Member (Teaching)')
                                            ->count(),
        ];

        // Get active committees for selection (for users who manage committees/boards)
        $committees = Committee::active()->withCount('users')->get();

        return view('admin.communication.create', compact('users', 'staffCategoryCounts', 'committees'));
    }

    public function store(Request $request)
    {
        $this->checkAdminAccess();
        
        // Determine if this is a draft or send action
        $isDraft = $request->input('action') === 'draft';
        
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:500',
            'message' => 'required|string',
            'recipient_type' => ['required', function ($attribute, $value, $fail) {
                $validTypes = ['all', 'selected', 'junior_staff', 'senior_staff', 'senior_member_non_teaching', 'senior_member_teaching'];
                // Also allow committee_* format
                if (!in_array($value, $validTypes) && !str_starts_with($value, 'committee_')) {
                    $fail('Invalid recipient type selected.');
                }
            }],
            'selected_users' => 'required_if:recipient_type,selected|array',
            'selected_users.*' => 'exists:users,id',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,jpg,png,gif,zip',
            'send_immediately' => 'boolean',
            'scheduled_at' => $isDraft ? 'nullable|date' : 'nullable|required_if:send_immediately,false|date|after:now',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle file uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('email_attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                ];
            }
        }

        // Get recipients using helper method
        $recipientUsers = $this->getRecipientsByType($request->recipient_type, $request->selected_users);

        // Create campaign
        $campaign = EmailCampaign::create([
            'subject' => $request->subject,
            'message' => $request->message,
            'attachments' => $attachmentPaths,
            'recipient_type' => $request->recipient_type,
            'selected_users' => $request->recipient_type === 'selected' ? $recipientUsers->pluck('id')->toArray() : null,
            'status' => $isDraft ? 'draft' : 'sending',
            'scheduled_at' => $isDraft ? null : ($request->scheduled_at ?? now()),
            'total_recipients' => $recipientUsers->count(),
            'created_by' => auth()->id(),
            'reference' => $this->generateUniqueReference(),
        ]);

        // If this is a draft, save and return immediately - NO EMAIL PROCESSING
        if ($isDraft) {
            return redirect()->route('admin.communication.index')
                            ->with('success', 'Memo saved as draft successfully!');
        }

        // ONLY EXECUTE EMAIL SENDING LOGIC FOR NON-DRAFT CAMPAIGNS
        // Create recipient records
        foreach ($recipientUsers as $user) {
            EmailCampaignRecipient::create([
                'comm_campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        }
        
        // Send emails directly using ResendMailService (reliable method)
        $sentCount = 0;
        $failedCount = 0;
        $resendService = new ResendMailService();

        foreach ($recipientUsers as $user) {
            try {

                // Generate HTML content for this recipient (like the job does)
                $htmlContent = view('mails.campaign_simple', [
                    'campaign' => $campaign,
                    'user' => $user,
                    'subject' => $campaign->subject,
                    'message' => $campaign->message,
                ])->render();

                // Prepare attachments for Resend API
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
                
                // Send email using ResendMailService directly (proven to work)
                $fromName = $campaign->creator ? trim($campaign->creator->first_name . ' ' . $campaign->creator->last_name) : (config('mail.from.name') ?: 'CUG');
                $result = $resendService->sendEmail(
                    $user->email,
                    $campaign->subject,
                    $htmlContent,
                    config('mail.from.address'),
                    $attachments,
                    0,
                    $fromName
                );

                if ($result['success']) {
                    // Mark recipient as sent
                    EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'sent',
                            'sent_at' => now()
                        ]);
                    
                    $sentCount++;
                    
                } else {
                    // Mark recipient as failed
                    $error = $result['error'] ?? 'Unknown error';
                    EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'failed',
                            'error_message' => 'ResendMailService error: ' . $error
                        ]);
                    
                    $failedCount++;
                    
                }
                
                // Rate limiting delay (Resend allows 2 emails per second)
                usleep(500000); // 0.5 second
                
            } catch (Exception $e) {
                // Mark recipient as failed
                EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                    ->where('user_id', $user->id)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
                
                $failedCount++;
            }
        }

        // Update campaign final status for sent campaigns
        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);

        return redirect()->route('admin.communication.index')
                        ->with('success', "Memo campaign sent successfully! Sent: {$sentCount}, Failed: {$failedCount}")
                        ->with('memo_delivered', true);
    }

    private function generateUniqueReference(): string
    {
        do {
            $random = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $reference = 'Ref-CUG' . $random;
        } while (EmailCampaign::where('reference', $reference)->exists());

        return $reference;
    }

    public function show(EmailCampaign $campaign)
    {
        $this->checkAdminAccess();
        
        // Ensure user can only view their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        $campaign->load(['creator', 'recipients.user']);
        
        return view('admin.communication.show', compact('campaign'));
    }

    public function edit(EmailCampaign $campaign)
    {
        $this->checkAdminAccess();
        
        // Ensure user can only edit their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        if ($campaign->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft memos can be edited.');
        }

        $users = User::where('is_approve', true)
                    ->select('id', 'first_name', 'last_name', 'email')
                    ->orderBy('first_name')
                    ->get();

        return view('admin.communication.edit', compact('campaign', 'users'));
    }

    public function update(Request $request, EmailCampaign $campaign)
    {
        $this->checkAdminAccess();
        
        // Ensure user can only update their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        if ($campaign->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft memos can be updated.');
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:500',
            'message' => 'required|string',
            'recipient_type' => ['required', function ($attribute, $value, $fail) {
                $validTypes = ['all', 'selected', 'junior_staff', 'senior_staff', 'senior_member_non_teaching', 'senior_member_teaching'];
                // Also allow committee_* format
                if (!in_array($value, $validTypes) && !str_starts_with($value, 'committee_')) {
                    $fail('Invalid recipient type selected.');
                }
            }],
            'selected_users' => 'required_if:recipient_type,selected|array',
            'selected_users.*' => 'exists:users,id',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,jpg,png,gif,zip',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle new file uploads
        $attachmentPaths = $campaign->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('email_attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                ];
            }
        }

        // Remove old recipients
        $campaign->recipients()->delete();

        // Determine new recipients
        $recipients = $request->recipient_type === 'all' 
            ? User::where('is_approve', true)->pluck('id')->toArray()
            : $request->selected_users;

        // Update campaign
        $campaign->update([
            'subject' => $request->subject,
            'message' => $request->message,
            'attachments' => $attachmentPaths,
            'recipient_type' => $request->recipient_type,
            'selected_users' => $request->recipient_type === 'selected' ? $recipients : null,
            'total_recipients' => count($recipients),
        ]);

        // For drafts, we don't create recipient records until they're ready to send
        // Recipient records will be created when the draft is actually sent

        return redirect()->route('admin.communication.show', $campaign)
                        ->with('success', 'Memo updated successfully!');
    }

    public function destroy(EmailCampaign $campaign)
    {
        $this->checkAdminAccess();
        
        // Ensure user can only delete their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }

        // Delete attachment files
        if ($campaign->attachments) {
            foreach ($campaign->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }

        $campaign->delete();

        return redirect()->route('admin.communication.index')
                        ->with('success', 'Memo deleted successfully!');
    }

    public function send(EmailCampaign $campaign)
    {
        $this->checkAdminAccess();
        
        // Ensure user can only send their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        if ($campaign->status !== 'draft' && $campaign->status !== 'scheduled') {
            return redirect()->back()->with('error', 'Memo cannot be sent in current status.');
        }

        // Determine recipients based on campaign settings using helper method
        $recipientUsers = $this->getRecipientsByType($campaign->recipient_type, $campaign->selected_users);

        // Ensure recipient records exist (create if draft or missing)
        if ($campaign->status === 'draft' || $campaign->recipients()->count() === 0) {
            foreach ($recipientUsers as $user) {
                EmailCampaignRecipient::firstOrCreate([
                    'comm_campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                ], [
                    'status' => 'pending',
                ]);
            }

            $campaign->update(['total_recipients' => $recipientUsers->count()]);
        }

        // Mark as sending
        $campaign->update([
            'status' => 'sending',
            'scheduled_at' => now(),
        ]);

        // Send emails synchronously (no queue dependency) using ResendMailService
        $sentCount = 0;
        $failedCount = 0;
        $resendService = new ResendMailService();

        foreach ($recipientUsers as $user) {
            try {
                // Generate HTML content for this recipient
                $htmlContent = view('mails.campaign_simple', [
                    'campaign' => $campaign,
                    'user' => $user,
                    'subject' => $campaign->subject,
                    'message' => $campaign->message,
                ])->render();

                // Prepare attachments
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

                // Send email
                $fromName = $campaign->creator ? trim($campaign->creator->first_name . ' ' . $campaign->creator->last_name) : (config('mail.from.name') ?: 'CUG');
                $result = $resendService->sendEmail(
                    $user->email,
                    $campaign->subject,
                    $htmlContent,
                    config('mail.from.address'),
                    $attachments,
                    0,
                    $fromName
                );

                if ($result['success']) {
                    EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'sent',
                            'sent_at' => now()
                        ]);
                    $sentCount++;
                } else {
                    $error = $result['error'] ?? 'Unknown error';
                    EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'failed',
                            'error_message' => 'ResendMailService error: ' . $error
                        ]);
                    $failedCount++;
                }

                // Rate limiting delay
                usleep(500000);

            } catch (Exception $e) {
                EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                    ->where('user_id', $user->id)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
                $failedCount++;
            }
        }

        // Finalize campaign status
        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);

        return redirect()->back()->with('success', "Memo campaign sent successfully! Sent: {$sentCount}, Failed: {$failedCount}");
    }

    public function removeAttachment(EmailCampaign $campaign, $attachmentIndex)
    {
        $this->checkAdminAccess();
        
        if ($campaign->status !== 'draft') {
            return response()->json(['error' => 'Only draft emails can be modified.'], 400);
        }

        $attachments = $campaign->attachments ?? [];
        
        if (isset($attachments[$attachmentIndex])) {
            // Delete file from storage
            Storage::disk('public')->delete($attachments[$attachmentIndex]['path']);
            
            // Remove from array
            unset($attachments[$attachmentIndex]);
            
            // Re-index array
            $attachments = array_values($attachments);
            
            // Update email
            $campaign->update(['attachments' => $attachments]);
            
            return response()->json(['success' => 'Attachment removed successfully.']);
        }

        return response()->json(['error' => 'Attachment not found.'], 404);
    }

    public function downloadAttachment(EmailCampaign $campaign, $attachmentIndex)
    {
        $this->checkAdminAccess();
        
        $attachments = $campaign->attachments ?? [];
        
        if (isset($attachments[$attachmentIndex])) {
            $attachment = $attachments[$attachmentIndex];
            $filePath = storage_path('app/public/' . $attachment['path']);
            
            if (file_exists($filePath)) {
                return response()->download($filePath, $attachment['name']);
            }
        }

        return redirect()->back()->with('error', 'Attachment not found.');
    }

    public function getUsersAjax(Request $request)
    {
        $this->checkAdminAccess();
        
        $search = $request->get('search', '');
        
        $users = User::where('is_approve', true)
                    ->where(function($query) use ($search) {
                        $query->where('first_name', 'LIKE', "%{$search}%")
                              ->orWhere('last_name', 'LIKE', "%{$search}%")
                              ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->select('id', 'first_name', 'last_name', 'email')
                    ->limit(20)
                    ->get()
                    ->map(function($user) {
                        return [
                            'id' => $user->id,
                            'text' => $user->first_name . ' ' . $user->last_name . ' (' . $user->email . ')'
                        ];
                    });

        return response()->json($users);
    }

    public function statistics()
    {
        $this->checkAdminAccess();
        
        $stats = [
            'total_campaigns' => EmailCampaign::where('created_by', auth()->id())->count(),
            'sent_campaigns' => EmailCampaign::where('created_by', auth()->id())->byStatus('sent')->count(),
            'draft_campaigns' => EmailCampaign::where('created_by', auth()->id())->byStatus('draft')->count(),
            'scheduled_campaigns' => EmailCampaign::where('created_by', auth()->id())->byStatus('scheduled')->count(),
            'total_emails_sent' => EmailCampaignRecipient::whereHas('campaign', function($q) {
                $q->where('created_by', auth()->id());
            })->where('status', 'sent')->count(),
            'total_users' => User::where('is_approve', true)->count(),
        ];

        // Get recent email activity for current user only
        $recentActivity = EmailCampaign::with('creator')
                                    ->where('created_by', auth()->id())
                                    ->recentFirst()
                                    ->limit(5)
                                    ->get();

        // Get monthly statistics for current user only
        $monthlyStats = EmailCampaign::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                                   ->where('created_by', auth()->id())
                                   ->where('created_at', '>=', Carbon::now()->subMonths(12))
                                   ->groupBy('month')
                                   ->orderBy('month')
                                   ->get();

        return view('admin.communication.statistics', compact('stats', 'recentActivity', 'monthlyStats'));
    }

    public function viewReplies(EmailCampaign $campaign)
    {
        $this->checkAdminAccess();
        
        // Ensure user can only view replies to their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        $replies = $campaign->replies()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.communication.replies', compact('campaign', 'replies'));
    }

    // ==================== ADMIN METHODS ====================
    
    private function checkAdminOnlyAccess()
    {
        // Only Admin (role='user') users are allowed to access admin communication pages
        if (!auth()->check() || (!auth()->user()->isRegularUser() && !auth()->user()->isSuperAdmin())) {
            abort(403, 'Unauthorized action. Admin access required.');
        }
    }

    public function adminIndex(Request $request)
    {
        $this->checkAdminOnlyAccess();
        
        // Get filter parameter
        $statusFilter = $request->get('status', 'all');
        $search = $request->get('search');
        
        // Build query based on filter - ONLY show campaigns created by current admin user
        $query = EmailCampaign::with(['creator', 'recipients'])
            ->where('created_by', auth()->id())
            ->recentFirst();
        
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        $campaigns = $query->paginate(10);
        
        // Get statistics for current admin user only
        $stats = [
            'total' => EmailCampaign::where('created_by', auth()->id())->count(),
            'draft' => EmailCampaign::where('created_by', auth()->id())->where('status', 'draft')->count(),
            'scheduled' => EmailCampaign::where('created_by', auth()->id())->where('status', 'scheduled')->count(),
            'sending' => EmailCampaign::where('created_by', auth()->id())->where('status', 'sending')->count(),
            'sent' => EmailCampaign::where('created_by', auth()->id())->where('status', 'sent')->count(),
            'failed' => EmailCampaign::where('created_by', auth()->id())->where('status', 'failed')->count(),
        ];
        
        return view('admin.communication-admin.index', compact('campaigns', 'stats', 'statusFilter', 'search'));
    }

    public function adminCreate()
    {
        $this->checkAdminOnlyAccess();
        
        $users = User::where('is_approve', true)
                    ->with('position')
                    ->select('id', 'first_name', 'last_name', 'email', 'position_id')
                    ->orderBy('first_name')
                    ->get();

        // Calculate staff category counts
        $staffCategoryCounts = [
            'junior_staff' => User::where('is_approve', true)
                                ->where('staff_category', 'Junior Staff')
                                ->count(),
            'senior_staff' => User::where('is_approve', true)
                                ->where('staff_category', 'Senior Staff')
                                ->count(),
            'senior_member_non_teaching' => User::where('is_approve', true)
                                                ->where('staff_category', 'Senior Member (Non-Teaching)')
                                                ->count(),
            'senior_member_teaching' => User::where('is_approve', true)
                                            ->where('staff_category', 'Senior Member (Teaching)')
                                            ->count(),
        ];

        return view('admin.communication-admin.create', compact('users', 'staffCategoryCounts'));
    }

    public function adminStore(Request $request)
    {
        $this->checkAdminOnlyAccess();
        
        // Determine if this is a draft or send action
        $isDraft = $request->input('action') === 'draft';
        
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:500',
            'message' => 'required|string',
            'recipient_type' => ['required', function ($attribute, $value, $fail) {
                $validTypes = ['all', 'selected', 'junior_staff', 'senior_staff', 'senior_member_non_teaching', 'senior_member_teaching'];
                // Also allow committee_* format
                if (!in_array($value, $validTypes) && !str_starts_with($value, 'committee_')) {
                    $fail('Invalid recipient type selected.');
                }
            }],
            'selected_users' => 'required_if:recipient_type,selected|array',
            'selected_users.*' => 'exists:users,id',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,jpg,png,gif,zip',
            'send_immediately' => 'boolean',
            'scheduled_at' => $isDraft ? 'nullable|date' : 'nullable|required_if:send_immediately,false|date|after:now',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle file uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('email_attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                ];
            }
        }

        // Get recipients using helper method
        $recipientUsers = $this->getRecipientsByType($request->recipient_type, $request->selected_users);

        // Create campaign
        $campaign = EmailCampaign::create([
            'subject' => $request->subject,
            'message' => $request->message,
            'attachments' => $attachmentPaths,
            'recipient_type' => $request->recipient_type,
            'selected_users' => $request->recipient_type === 'selected' ? $recipientUsers->pluck('id')->toArray() : null,
            'status' => $isDraft ? 'draft' : 'sending',
            'scheduled_at' => $isDraft ? null : ($request->scheduled_at ?? now()),
            'total_recipients' => $recipientUsers->count(),
            'created_by' => auth()->id(),
            'reference' => $this->generateUniqueReference(),
        ]);

        // If this is a draft, save and return immediately - NO EMAIL PROCESSING
        if ($isDraft) {
            return redirect()->route('admin.communication-admin.index')
                            ->with('success', 'Memo saved as draft successfully!');
        }

        // ONLY EXECUTE EMAIL SENDING LOGIC FOR NON-DRAFT CAMPAIGNS
        // Create recipient records
        foreach ($recipientUsers as $user) {
            EmailCampaignRecipient::create([
                'comm_campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        }
        
        // Send emails directly using ResendMailService (reliable method)
        $sentCount = 0;
        $failedCount = 0;
        $resendService = new ResendMailService();

        foreach ($recipientUsers as $user) {
            try {
                // Generate HTML content for this recipient (like the job does)
                $htmlContent = view('mails.campaign_simple', [
                    'campaign' => $campaign,
                    'user' => $user,
                    'subject' => $campaign->subject,
                    'message' => $campaign->message,
                ])->render();

                // Prepare attachments for Resend API
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
                
                // Send email using ResendMailService directly (proven to work)
                $fromName = $campaign->creator ? trim($campaign->creator->first_name . ' ' . $campaign->creator->last_name) : (config('mail.from.name') ?: 'CUG');
                $result = $resendService->sendEmail(
                    $user->email,
                    $campaign->subject,
                    $htmlContent,
                    config('mail.from.address'),
                    $attachments,
                    0,
                    $fromName
                );

                if ($result['success']) {
                    $sentCount++;
                    // Update recipient status
                    EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'sent',
                            'sent_at' => now()
                        ]);
                } else {
                    $failedCount++;
                    // Update recipient status with error
                    $error = $result['error'] ?? 'Unknown error';
                    EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'failed',
                            'error_message' => 'ResendMailService error: ' . $error
                        ]);
                }

                // Rate limiting delay to avoid provider throttling
                usleep(500000);
            } catch (Exception $e) {
                $failedCount++;
                Log::error('Campaign email failed: ' . $e->getMessage(), [
                    'campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                
                // Update recipient status
                EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                    ->where('user_id', $user->id)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
            }
        }

        // Update campaign status
        $finalStatus = $failedCount > 0 ? ($sentCount > 0 ? 'partial' : 'failed') : 'sent';
        $campaign->update([
            'status' => $finalStatus,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);

        $message = $finalStatus === 'sent' 
            ? "Memo sent successfully to {$sentCount} recipients!"
            : "Memo sent with issues. {$sentCount} successful, {$failedCount} failed.";

        return redirect()->route('admin.communication-admin.index')
                        ->with('success', $message)
                        ->with('memo_delivered', true);
    }

    public function adminShow(EmailCampaign $campaign)
    {
        $this->checkAdminOnlyAccess();
        
        // Ensure admin can only view their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        $campaign->load(['creator', 'recipients.user']);
        
        return view('admin.communication-admin.show', compact('campaign'));
    }

    public function adminEdit(EmailCampaign $campaign)
    {
        $this->checkAdminOnlyAccess();
        
        // Ensure admin can only edit their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        $users = User::where('is_approve', true)
                    ->select('id', 'first_name', 'last_name', 'email')
                    ->orderBy('first_name')
                    ->get();

        return view('admin.communication-admin.edit', compact('campaign', 'users'));
    }

    public function adminUpdate(Request $request, EmailCampaign $campaign)
    {
        $this->checkAdminOnlyAccess();
        
        // Ensure admin can only update their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        // Determine if this is a draft or send action
        $isDraft = $request->input('action') === 'draft';
        
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:500',
            'message' => 'required|string',
            'recipient_type' => ['required', function ($attribute, $value, $fail) {
                $validTypes = ['all', 'selected', 'junior_staff', 'senior_staff', 'senior_member_non_teaching', 'senior_member_teaching'];
                // Also allow committee_* format
                if (!in_array($value, $validTypes) && !str_starts_with($value, 'committee_')) {
                    $fail('Invalid recipient type selected.');
                }
            }],
            'selected_users' => 'required_if:recipient_type,selected|array',
            'selected_users.*' => 'exists:users,id',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,jpg,png,gif,zip',
            'send_immediately' => 'boolean',
            'scheduled_at' => $isDraft ? 'nullable|date' : 'nullable|required_if:send_immediately,false|date|after:now',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Handle file uploads
        $attachmentPaths = $campaign->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('email_attachments', 'public');
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType(),
                ];
            }
        }

        // Get recipients using helper method
        $recipientUsers = $this->getRecipientsByType($request->recipient_type, $request->selected_users);

        // Update campaign
        $campaign->update([
            'subject' => $request->subject,
            'message' => $request->message,
            'attachments' => $attachmentPaths,
            'recipient_type' => $request->recipient_type,
            'selected_users' => $request->recipient_type === 'selected' ? $recipientUsers->pluck('id')->toArray() : null,
            'status' => $isDraft ? 'draft' : 'sending',
            'scheduled_at' => $isDraft ? null : ($request->scheduled_at ?? now()),
            'total_recipients' => $recipientUsers->count(),
        ]);

        // If this is a draft, save and return immediately - NO EMAIL PROCESSING
        if ($isDraft) {
            return redirect()->route('admin.communication-admin.index')
                            ->with('success', 'Memo updated and saved as draft successfully!');
        }

        // Clear existing recipients and create new ones
        $campaign->recipients()->delete();
        foreach ($recipientUsers as $user) {
            EmailCampaignRecipient::create([
                'comm_campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        }
        
        // Send emails directly using ResendMailService (reliable method)
        $sentCount = 0;
        $failedCount = 0;
        $resendService = new ResendMailService();

        foreach ($recipientUsers as $user) {
            try {
                // Generate HTML content for this recipient (like the job does)
                $htmlContent = view('mails.campaign_simple', [
                    'campaign' => $campaign,
                    'user' => $user,
                    'subject' => $campaign->subject,
                    'message' => $campaign->message,
                ])->render();

                // Prepare attachments for Resend API
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
                
                // Send email using ResendMailService directly (proven to work)
                $fromName = $campaign->creator ? trim($campaign->creator->first_name . ' ' . $campaign->creator->last_name) : (config('mail.from.name') ?: 'CUG');
                $result = $resendService->sendEmail(
                    $user->email,
                    $campaign->subject,
                    $htmlContent,
                    config('mail.from.address'),
                    $attachments,
                    0,
                    $fromName
                );

                if ($result['success']) {
                    $sentCount++;
                    // Update recipient status
                    EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'sent',
                            'sent_at' => now()
                        ]);
                } else {
                    $failedCount++;
                    // Update recipient status with error
                    $error = $result['error'] ?? 'Unknown error';
                    EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'failed',
                            'error_message' => 'ResendMailService error: ' . $error
                        ]);
                }

                // Rate limiting delay to avoid provider throttling
                usleep(500000);
            } catch (Exception $e) {
                $failedCount++;
                Log::error('Campaign email failed: ' . $e->getMessage(), [
                    'campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                
                // Update recipient status
                EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                    ->where('user_id', $user->id)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
            }
        }

        // Update campaign status
        $finalStatus = $failedCount > 0 ? ($sentCount > 0 ? 'partial' : 'failed') : 'sent';
        $campaign->update([
            'status' => $finalStatus,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);

        $message = $finalStatus === 'sent' 
            ? "Memo sent successfully to {$sentCount} recipients!"
            : "Memo sent with issues. {$sentCount} successful, {$failedCount} failed.";

        return redirect()->route('admin.communication-admin.index')
                        ->with('success', $message)
                        ->with('memo_delivered', true);
    }

    public function adminDestroy(EmailCampaign $campaign)
    {
        $this->checkAdminOnlyAccess();
        
        // Ensure admin can only delete their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        // Delete associated recipients first
        $campaign->recipients()->delete();
        
        // Delete campaign
        $campaign->delete();
        
        return redirect()->route('admin.communication-admin.index')
                        ->with('success', 'Memo deleted successfully!');
    }

    public function adminSend(EmailCampaign $campaign)
    {
        $this->checkAdminOnlyAccess();
        
        // Ensure admin can only send their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        if ($campaign->status !== 'draft' && $campaign->status !== 'scheduled') {
            return redirect()->back()->with('error', 'Memo cannot be sent in current status.');
        }

        // Determine recipients based on campaign settings using helper method
        $recipientUsers = $this->getRecipientsByType($campaign->recipient_type, $campaign->selected_users);

        // Ensure recipient records exist (create if draft or missing)
        if ($campaign->status === 'draft' || $campaign->recipients()->count() === 0) {
            foreach ($recipientUsers as $user) {
                EmailCampaignRecipient::firstOrCreate([
                    'comm_campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                ], [
                    'status' => 'pending',
                ]);
            }

            $campaign->update(['total_recipients' => $recipientUsers->count()]);
        }

        // Mark as sending
        $campaign->update([
            'status' => 'sending',
            'scheduled_at' => now(),
        ]);

        // Send emails synchronously (no queue dependency) using ResendMailService
        $sentCount = 0;
        $failedCount = 0;
        $resendService = new ResendMailService();

        foreach ($recipientUsers as $user) {
            try {
                // Generate HTML content for this recipient (like the job does)
                $htmlContent = view('mails.campaign_simple', [
                    'campaign' => $campaign,
                    'user' => $user,
                    'subject' => $campaign->subject,
                    'message' => $campaign->message,
                ])->render();

                // Prepare attachments for Resend API
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
                
                // Send email using ResendMailService directly (proven to work)
                $fromName = $campaign->creator ? trim($campaign->creator->first_name . ' ' . $campaign->creator->last_name) : (config('mail.from.name') ?: 'CUG');
                $result = $resendService->sendEmail(
                    $user->email,
                    $campaign->subject,
                    $htmlContent,
                    config('mail.from.address'),
                    $attachments,
                    0,
                    $fromName
                );

                if ($result['success']) {
                    $sentCount++;
                    // Update recipient status
                    EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'sent',
                            'sent_at' => now()
                        ]);
                } else {
                    $failedCount++;
                    // Update recipient status with error
                    $error = $result['error'] ?? 'Unknown error';
                    EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                        ->where('user_id', $user->id)
                        ->update([
                            'status' => 'failed',
                            'error_message' => 'ResendMailService error: ' . $error
                        ]);
                }

                // Rate limiting delay to avoid provider throttling
                usleep(500000);
            } catch (Exception $e) {
                $failedCount++;
                Log::error('Campaign email failed: ' . $e->getMessage(), [
                    'campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                
                // Update recipient status
                EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                    ->where('user_id', $user->id)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
            }
        }

        // Update campaign status
        $finalStatus = $failedCount > 0 ? ($sentCount > 0 ? 'partial' : 'failed') : 'sent';
        $campaign->update([
            'status' => $finalStatus,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);

        $message = $finalStatus === 'sent' 
            ? "Memo sent successfully to {$sentCount} recipients!"
            : "Memo sent with issues. {$sentCount} successful, {$failedCount} failed.";

        return redirect()->route('admin.communication-admin.index')
                        ->with('success', $message)
                        ->with('memo_delivered', true);
    }

    public function adminStatistics()
    {
        $this->checkAdminOnlyAccess();
        
        $stats = [
            'total_campaigns' => EmailCampaign::where('created_by', auth()->id())->count(),
            'sent_campaigns' => EmailCampaign::where('created_by', auth()->id())->byStatus('sent')->count(),
            'draft_campaigns' => EmailCampaign::where('created_by', auth()->id())->byStatus('draft')->count(),
            'scheduled_campaigns' => EmailCampaign::where('created_by', auth()->id())->byStatus('scheduled')->count(),
            'total_emails_sent' => EmailCampaignRecipient::whereHas('campaign', function($q) {
                $q->where('created_by', auth()->id());
            })->where('status', 'sent')->count(),
            'total_users' => User::where('is_approve', true)->count(),
        ];

        // Get recent email activity for current admin user only
        $recentActivity = EmailCampaign::with('creator')
                                    ->where('created_by', auth()->id())
                                    ->recentFirst()
                                    ->limit(5)
                                    ->get();

        // Get monthly statistics for current admin user only
        $monthlyStats = EmailCampaign::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                                   ->where('created_by', auth()->id())
                                   ->where('created_at', '>=', Carbon::now()->subMonths(12))
                                   ->groupBy('month')
                                   ->orderBy('month')
                                   ->get();

        return view('admin.communication-admin.statistics', compact('stats', 'recentActivity', 'monthlyStats'));
    }

    public function adminViewReplies(EmailCampaign $campaign)
    {
        $this->checkAdminOnlyAccess();
        
        // Ensure admin can only view replies to their own campaigns
        if ($campaign->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this memo.');
        }
        
        $replies = $campaign->replies()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.communication-admin.replies', compact('campaign', 'replies'));
    }

    public function adminDownloadAttachment(EmailCampaign $campaign, $index)
    {
        $this->checkAdminOnlyAccess();
        
        $attachments = $campaign->attachments;
        if (!$attachments || !isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }

        $attachment = $attachments[$index];
        $filePath = storage_path('app/public/' . $attachment['path']);

        if (!file_exists($filePath)) {
            abort(404, 'File not found on disk.');
        }

        return response()->download($filePath, $attachment['name']);
    }

    public function adminRemoveAttachment(EmailCampaign $campaign, $index)
    {
        $this->checkAdminOnlyAccess();
        
        $attachments = $campaign->attachments;
        if (!$attachments || !isset($attachments[$index])) {
            return response()->json(['success' => false, 'message' => 'Attachment not found.']);
        }

        // Delete file from storage
        $attachment = $attachments[$index];
        $filePath = storage_path('app/public/' . $attachment['path']);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Remove from array
        unset($attachments[$index]);
        $attachments = array_values($attachments); // Re-index array

        $campaign->update(['attachments' => $attachments]);

        return response()->json(['success' => true, 'message' => 'Attachment removed successfully.']);
    }

    public function adminGetUsersAjax(Request $request)
    {
        $this->checkAdminOnlyAccess();
        
        $search = $request->get('search', '');
        
        $users = User::where('is_approve', true)
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->select('id', 'first_name', 'last_name', 'email')
            ->limit(20)
            ->get();

        return response()->json($users);
    }

    /**
     * Get recipients based on recipient type
     */
    private function getRecipientsByType($recipientType, $selectedUsers = null)
    {
        if ($recipientType === 'all') {
            return User::where('is_approve', true)->get();
        } elseif ($recipientType === 'selected') {
            // Ensure selectedUsers is an array and not null
            if (empty($selectedUsers) || !is_array($selectedUsers)) {
                return collect();
            }
            return User::whereIn('id', $selectedUsers)->where('is_approve', true)->get();
        } elseif (in_array($recipientType, ['junior_staff', 'senior_staff', 'senior_member_non_teaching', 'senior_member_teaching'])) {
            // Map recipient types to staff categories
            $staffCategoryMap = [
                'junior_staff' => 'Junior Staff',
                'senior_staff' => 'Senior Staff',
                'senior_member_non_teaching' => 'Senior Member (Non-Teaching)',
                'senior_member_teaching' => 'Senior Member (Teaching)'
            ];
            
            $staffCategory = $staffCategoryMap[$recipientType];
            return User::where('is_approve', true)
                      ->where('staff_category', $staffCategory)
                      ->get();
        } elseif (str_starts_with($recipientType, 'committee_')) {
            // Handle committee/board recipient type
            $committeeId = str_replace('committee_', '', $recipientType);
            $committee = Committee::find($committeeId);
            
            if ($committee) {
                // Get all users who belong to this committee
                return $committee->users()->where('is_approve', true)->get();
            }
            
            return collect();
        } else {
            return collect();
        }
    }
}