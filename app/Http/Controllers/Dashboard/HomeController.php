<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Mail\Approval;
use App\Models\Academic;
use App\Models\Department;
use App\Models\File;
use App\Models\Position;
use App\Models\Message;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\MemoReply;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Exam;
use App\Models\User;
use App\Models\Visit;
use App\Models\Committee;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\ResendMailService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    public function dashboard(){
        $currentTime = Carbon::now();
        $twentyFourHoursAgo = $currentTime->subHours(24);
        $dailyVisitCount = Visit::where('visited_at', '>=', $twentyFourHoursAgo)->count();
        $numberOfExamsToFetch = 2;
        $files = File::all();
        // Get user's committees/boards
        $userCommittees = Auth::user()->committees()->with('users')->get();
        
        return view('admin.dashboard',[
            'total_papers' => Exam::count(),
            'total_users' => User::count(),
            'dailyVisits' => $dailyVisitCount,
            'totalVisits' => Visit::all()->count(),
            'admin_total_papers' => Exam::where('user_id', Auth::user()->id)->count(),
            'recentlyUploadedExams' => Exam::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->take($numberOfExamsToFetch)->get(),
            'total_files' => $files->count(),
            'admin_total_files' => File::where('user_id', Auth::user()->id)->count(),
            'userCommittees' => $userCommittees,
        ]);
    }

    public function create(){
        return view('admin.deposition_form',[
            'departments' => Department::all(),
            'years' => Academic::all(),
        ]);
    }

    public function createFile(){
        return view('admin.file_form');
    }

    public function document(){
        //super admin
        $allExams = Exam::all();
        $files = File::all();
        $exams = Exam::all();

        if (Auth::user()->is_admin == 1) {
            $allExams = Exam::where('user_id', Auth::user()->id)->get();
            $files = File::where('user_id', Auth::user()->id)->get();
            $exams = Exam::where('user_id', Auth::user()->id)->get();

        }

        $results = [
            'exams' => $allExams,
            'files' => $files,
        ];

        $uniqueFaculties = $exams->pluck('faculty')->unique()->values()->all();
        $uniqueTags = $exams->pluck('tags')->unique()->values()->all();
        $uniqueSemesters = $exams->pluck('semester')->unique()->values()->all();

        return view('admin.documents',[
            'exams' => $results,
            'faculties' => $uniqueFaculties,
            'tags' => $uniqueTags,
            'semesters' => $uniqueSemesters,
            'years' => Exam::select(DB::raw('YEAR(created_at) as year'))->distinct()->pluck('year'),

        ]);
    }

    public function uploadedDocument(){
        $exams = Exam::where('user_id', Auth::user()->id)->get();
        return view('admin.uploaded_documents',compact('exams'));
    }

    public function allUploadedDocument(){
        // Only show user's own exams (no approval system means users manage their own content)
        $exams = Exam::where('user_id', Auth::user()->id)->get();
        return view('admin.all_uploaded_documents',compact('exams'));
    }

    // Unified Exams view (no more pending/approved separation)
    public function myExams(){
        $exams = Exam::where('user_id', Auth::user()->id)->get();
        return view('admin.my_exams',compact('exams'));
    }

    public function allExams(){
        // Only show user's own exams (no approval system means users manage their own content)
        $exams = Exam::where('user_id', Auth::user()->id)->get();
        return view('admin.all_exams',compact('exams'));
    }



    public function message(){
        $userId = Auth::id();
        $memos = EmailCampaignRecipient::with(['campaign.creator'])
            ->where('user_id', $userId)
            ->orderBy('created_at','desc')
            ->get();

        return view('admin.message',[
            'messages' => $memos,
        ]);
    }

    public function readMemo(EmailCampaignRecipient $recipient)
    {
        abort_unless($recipient->user_id === Auth::id(), 403);
        $recipient->load('campaign');
        
        // Mark as read when viewing (now that fillable is fixed)
        $recipient->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        
        return view('admin.view_message', [
            'message' => (object) [
                'id' => $recipient->id,
                'title' => $recipient->campaign->subject,
                'body' => $recipient->campaign->message,
                'created_at' => $recipient->created_at,
                'attachments' => $recipient->campaign->attachments,
            ]
        ]);
    }

    public function markAllMemosRead()
    {
        $userId = Auth::id();
        EmailCampaignRecipient::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return redirect()->back();
    }

    public function markSingleMemoAsRead(EmailCampaignRecipient $recipient)
    {
        abort_unless($recipient->user_id === Auth::id(), 403);
        
        $recipient->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Memo marked as read successfully!');
    }

    public function unreadMemoCount()
    {
        $userId = Auth::id();
        $unreadCount = EmailCampaignRecipient::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'unread' => $unreadCount
        ]);
    }

    public function recentMemos()
    {
        $recentMemos = EmailCampaignRecipient::with('campaign')
            ->where('user_id', Auth::id())
            ->where('is_read', false) // Only show unread memos in notification tray
            ->orderBy('created_at','desc')
            ->limit(5)
            ->get();

        $memos = $recentMemos->map(function($rm) {
            // Strip HTML tags and get plain text preview
            $messagePreview = strip_tags($rm->campaign->message ?? '');
            $messagePreview = \Str::limit($messagePreview, 100);
            
            return [
                'id' => $rm->id,
                'subject' => \Str::limit($rm->campaign->subject, 40),
                'message' => $messagePreview,
                'created_at' => $rm->created_at->diffForHumans(),
                'is_read' => $rm->is_read,
                'url' => route('dashboard.memo.read', $rm->id)
            ];
        });

        return response()->json([
            'memos' => $memos
        ]);
    }


    public function downloadMemoAttachment(EmailCampaignRecipient $recipient, $index)
    {
        // Ensure the user can only download attachments from their own memos
        abort_unless($recipient->user_id === Auth::id(), 403);
        
        $recipient->load('campaign');
        $attachments = $recipient->campaign->attachments;
        
        // Check if the attachment index is valid
        if (!isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$index];
        $filePath = storage_path('app/public/' . $attachment['path']);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found on server.');
        }
        
        // Return file download response
        return response()->download($filePath, $attachment['name']);
    }

    public function replyToMemo(Request $request, EmailCampaignRecipient $recipient)
    {
        // Ensure the user can only reply to their own memos
        abort_unless($recipient->user_id === Auth::id(), 403);
        
        $request->validate([
            'message' => 'required|string|max:5000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,txt,jpg,jpeg,png,gif',
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('memo-replies', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        $reply = MemoReply::create([
            'campaign_id' => $recipient->comm_campaign_id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachments' => $attachments,
        ]);

        // Create notifications for ALL recipients of the memo (group replies)
        $campaign = $recipient->campaign;
        $replyAuthor = Auth::user()->first_name . ' ' . Auth::user()->last_name;
        
        // Get all recipients of this campaign
        $allRecipients = $campaign->recipients()->with('user')->get();
        
        foreach ($allRecipients as $campaignRecipient) {
            // Skip notifying the person who just replied
            if ($campaignRecipient->user_id === Auth::id()) {
                continue;
            }
            
            // Determine the correct route based on whether the recipient is an admin
            $recipientUser = $campaignRecipient->user;
            if ($recipientUser && $recipientUser->is_admin) {
                $repliesUrl = route('admin.communication-admin.replies', $campaign->id);
            } else {
                $repliesUrl = route('admin.communication.replies', $campaign->id);
            }
            
            Notification::createMemoReplyNotification(
                $campaignRecipient->user_id,
                $replyAuthor,
                $campaign->subject,
                $repliesUrl
            );
        }
        
        // Also notify the memo creator if they're not already a recipient
        if ($campaign->created_by !== Auth::id()) {
            $creator = User::find($campaign->created_by);
            if ($creator) {
                if ($creator->is_admin) {
                    $repliesUrl = route('admin.communication-admin.replies', $campaign->id);
                } else {
                    $repliesUrl = route('admin.communication.replies', $campaign->id);
                }
                
                Notification::createMemoReplyNotification(
                    $campaign->created_by,
                    $replyAuthor,
                    $campaign->subject,
                    $repliesUrl
                );
            }
        }

        return redirect()->back()->with('success', 'Reply sent successfully!');
    }

    public function viewMemoReplies(EmailCampaignRecipient $recipient)
    {
        $recipient->load('campaign');
        
        // Check if the user is either:
        // 1. The creator of the memo (sender) - can view all replies
        // 2. Any recipient of the memo - can view all replies from all recipients
        $isCreator = $recipient->campaign->created_by === Auth::id();
        $isRecipient = $recipient->user_id === Auth::id();
        
        // Also check if user is any recipient of this campaign (for group replies)
        $isAnyRecipient = $recipient->campaign->recipients()
            ->where('user_id', Auth::id())
            ->exists();
        
        abort_unless($isCreator || $isRecipient || $isAnyRecipient, 403, 'You do not have permission to view these replies.');
        
        // Show ALL replies to this campaign (group replies)
        $replies = $recipient->campaign->replies()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.memo-replies', compact('recipient', 'replies', 'isCreator'));
    }

    public function markReplyAsRead(MemoReply $reply)
    {
        // Ensure the user can only mark their own replies as read
        abort_unless($reply->user_id === Auth::id(), 403);
        
        $reply->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function downloadReplyAttachment(MemoReply $reply, $index)
    {
        // Ensure the user can only download attachments from their own replies or replies to their memos
        $userId = Auth::id();
        $canDownload = false;
        
        // Check if user is the author of the reply
        if ($reply->user_id === $userId) {
            $canDownload = true;
        }
        
        // Check if user is the creator of the original memo
        if (!$canDownload && $reply->campaign && $reply->campaign->created_by === $userId) {
            $canDownload = true;
        }
        
        if (!$canDownload) {
            abort(403, 'Unauthorized access to this attachment.');
        }
        
        $attachments = $reply->attachments;
        
        // Check if the attachment index is valid
        if (!isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$index];
        $filePath = storage_path('app/public/' . $attachment['path']);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found on server.');
        }
        
        // Return file download response
        return response()->download($filePath, $attachment['name']);
    }

    public function getNotifications()
    {
        $notifications = Notification::forUser(Auth::id())
            ->where('is_read', false) // Only show unread notifications in tray
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'url' => $notification->url,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                ];
            });

        return response()->json(['notifications' => $notifications]);
    }

    public function checkNewNotifications()
    {
        $hasNew = Notification::forUser(Auth::id())
            ->unread()
            ->where('created_at', '>', now()->subMinutes(5))
            ->exists();

        $replyCount = Notification::forUser(Auth::id())
            ->unread()
            ->count();

        return response()->json([
            'has_new' => $hasNew,
            'reply_count' => $replyCount
        ]);
    }

    public function markNotificationAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this notification.');
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllNotificationsAsRead()
    {
        Notification::forUser(Auth::id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function markAllUnifiedAsRead()
    {
        // Mark all notifications as read
        Notification::forUser(Auth::id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Mark all memos as read
        EmailCampaignRecipient::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function clearAllNotifications()
    {
        // Delete all notifications for the user
        Notification::forUser(Auth::id())->delete();

        // Mark all memos as read so they don't show in the tray
        EmailCampaignRecipient::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'All notifications cleared']);
    }

    public function profile(){

        return view('admin.profile',[
            'data' => User::findOrFail(Auth::user()->id),
        ]);
    }

    public function settings(){
        return view('admin.settings',[
            'data' => User::findOrFail(Auth::user()->id),
            'departments' => Department::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
        ]);
    }


    public function updateUserInfo(Request $request)
    {
        if ($request->input('position_id') === '' || $request->input('position_id') === null) {
            $request->merge(['position_id' => null]);
        }

        $staffCategoryRule = 'required|string|in:Junior Staff,Senior Staff,Senior Member (Non-Teaching),Senior Member (Teaching)';

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'department_id' => 'required|exists:departments,id',
            'staff_category' => $staffCategoryRule,
            'position_id' => 'nullable|exists:positions,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ], [
            'profile_picture.image' => 'The profile picture must be an image file.',
            'profile_picture.mimes' => 'The profile picture must be a JPEG, PNG, JPG, or GIF file.',
            'profile_picture.max' => 'The profile picture must not be larger than 5MB.',
            'department_id.required' => 'Please choose your Department/Faculty/Unit.',
            'staff_category.required' => 'Please choose your Staff Category.',
        ]);

        try {
            // Fetch authenticated user
            $user = Auth::user();

            // Update user information
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->email = $request->input('email');
            $user->department_id = $request->input('department_id');
            $user->staff_category = $request->input('staff_category');
            $user->position_id = $request->input('position_id');

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                
                // Additional validation
                if (!$file->isValid()) {
                    return redirect()->back()->withErrors(['profile_picture' => 'The uploaded file is not valid.'])->withInput();
                }

                // Delete old profile picture if exists
                if ($user->profile_picture) {
                    try {
                        $oldPath = public_path('profile_pictures/' . basename($user->profile_picture));
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    } catch (\Exception $e) {
                        // Log error but continue
                        \Log::warning('Failed to delete old profile picture: ' . $e->getMessage());
                    }
                }

                // Generate unique filename
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Store new profile picture in public/profile_pictures directory
                $file->move(public_path('profile_pictures'), $filename);
                $user->profile_picture = $filename;
            }

            // Save user
            $user->save();

            // Refresh the authenticated user session
            Auth::setUser($user);

            return redirect()->back()->with('success', 'Profile updated successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update profile. Please try again.'])->withInput();
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.min' => 'The new password must be at least 8 characters.',
        ]);

        try {
            $user = Auth::user();
            
            // Verify current password
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
            }
            
            // Update password and mark as changed
            $user->update([
                'password' => Hash::make($request->input('new_password')),
                'password_changed' => true
            ]);
            
            // Send password update confirmation email
            $emailSent = false;
            if (env('MAIL_MAILER') == 'resend') {
                try {
                    $resendService = new ResendMailService();
                    
                    $htmlContent = view('mails.password_updated', [
                        'firstname' => $user->first_name,
                        'email' => $user->email
                    ])->render();
                    
                    \Log::info('Attempting to send password update confirmation email', [
                        'user_email' => $user->email,
                        'mail_service' => 'resend'
                    ]);
                    
                    $response = $resendService->sendEmail(
                        $user->email,
                        'Password Updated Successfully - Your Account is Now Secure',
                        $htmlContent,
                        'cug@academicdigital.space'
                    );
                    
                    if ($response['success']) {
                        $emailSent = true;
                        \Log::info('Password update confirmation email sent successfully', [
                            'user_email' => $user->email,
                            'message_id' => $response['message_id'] ?? 'N/A'
                        ]);
                    } else {
                        \Log::error('Failed to send password update confirmation email', [
                            'user_email' => $user->email,
                            'error' => $response['error'] ?? 'Unknown error',
                            'response' => $response
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Exception while sending password update confirmation email', [
                        'user_email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $message = $emailSent 
                ? 'Password updated successfully! Your account is now more secure. A confirmation email has been sent.'
                : 'Password updated successfully! Your account is now more secure.';
                
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Password update failed: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update password. Please try again.'])->withInput();
        }
    }

    public function createMessage(){
        return view('admin.create_message');
    }

    public function users(Request $request){
        $perPage = $request->get('per_page', 15);
        $users = User::with('position')->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        $totalUsers = User::count();
        $approvedCount = User::where('is_approve', 1)->count();
        $pendingCount = User::where('is_approve', 0)->count();
        return view('admin.users',[
            'users' => $users,
            'totalUsers' => $totalUsers,
            'approvedCount' => $approvedCount,
            'pendingCount' => $pendingCount,
            'departments' => \App\Models\Department::orderBy('name')->get(),
            'positions' => \App\Models\Position::orderBy('name')->get(),
        ]);
    }

    public function storeUser(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'temporary_password' => 'required|string|min:8|confirmed',
            'department_id' => 'required|exists:departments,id',
            'staff_category' => 'required|string|in:Junior Staff,Senior Staff,Senior Member (Non-Teaching),Senior Member (Teaching)',
            'position_id' => 'nullable|exists:positions,id',
        ], [
            'temporary_password.confirmed' => 'The password confirmation does not match.',
            'temporary_password.min' => 'The password must be at least 8 characters.',
            'email.unique' => 'This email is already registered.',
        ]);

        try {
            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'is_admin' => 1, // Admin users (those who manage committees)
                'is_approve' => true, // Auto-approve since admin is adding them
                'password_changed' => false, // Require password change on first login
                'password' => Hash::make($validatedData['temporary_password']),
                'department_id' => $validatedData['department_id'],
                'staff_category' => $validatedData['staff_category'],
                'position_id' => $validatedData['position_id'] ?? null,
            ]);

            return redirect()->route('dashboard.users')
                ->with('success', "User '{$user->first_name} {$user->last_name}' has been added successfully. They will be required to change their password on first login.");
        } catch (\Exception $e) {
            \Log::error('Failed to add user: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to add user. Please try again.'])
                ->withInput();
        }
    }

    public function approve(User $user)
    {
        try {
            // Generate a temporary password for the user (firstname + 5 random numbers)
            // $randomNumbers = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            // $temporaryPassword = strtolower($user->first_name) . $randomNumbers;
            
            \Log::info('Starting user approval process', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                // 'temporary_password' => $temporaryPassword
            ]);
            
            // Update user with temporary password and approval status
            $user->update([
                'is_approve' => true,
                // 'password' => Hash::make($temporaryPassword),
                // 'password_changed' => false
                'password_changed' => true
            ]);
            
            \Log::info('User database updated successfully', [
                'user_id' => $user->id,
                'is_approve' => $user->is_approve,
                'password_changed' => $user->password_changed
            ]);
            
            // Send approval email with credentials (always attempt send)
            $emailSent = false;
            try {
                $resendService = new ResendMailService();
                
                $htmlContent = view('mails.approval', [
                    'firstname' => $user->first_name,
                    'email' => $user->email,
                    // 'temporaryPassword' => $temporaryPassword
                ])->render();
                
                \Log::info('Attempting to send approval email', [
                    'user_email' => $user->email
                ]);
                
                $response = $resendService->sendEmail(
                    $user->email,
                    'Account Successfully Approved - Your Login Credentials',
                    $htmlContent,
                    config('mail.from.address')
                );
                
                if (!empty($response['success'])) {
                    $emailSent = true;
                    \Log::info('Approval email sent successfully', [
                        'user_email' => $user->email,
                        'message_id' => $response['message_id'] ?? 'N/A'
                    ]);
                } else {
                    \Log::error('Failed to send approval email', [
                        'user_email' => $user->email,
                        'error' => $response['error'] ?? 'Unknown error',
                        'response' => $response
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Exception while sending approval email', [
                    'user_email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
            
            $message = $emailSent 
                ? 'User approved successfully and credentials sent via email'
                : 'User approved successfully, but email failed to send. Please check logs and notify user manually.';
                
            return redirect()->route('dashboard.users')->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Error during user approval process', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('dashboard.users')->with('error', 'Failed to approve user. Please try again or check the logs.');
        }
    }

    public function disapprove(User $user)
    {
        $user->update(['is_approve' => false]);

        return redirect()->route('dashboard.users')->with('success', 'User disapproved successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('dashboard.users')->with('success', 'User deleted successfully');
    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('frontend.welcome')->with('success', 'You have been logged out successfully.');
    }

    // ==================== UIMMS METHODS ====================
    
    /**
     * UIMMS Portal - Main dashboard for chat-based memo management
     */
    public function uimmsPortal()
    {
        $userId = Auth::id();
        
        try {
            // Get memo counts for each section using active participants OR recipients (for backward compatibility)
            // Pending memos: ALL active chats (all received/assigned memos)
            $pendingCount = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->count();
            
            $suspendedCount = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'suspended')->count();
            
            $completedCount = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'completed')->count();
            
            $archivedCount = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'archived')->count();

            return view('admin.uimms.portal', compact(
                'pendingCount', 
                'suspendedCount', 
                'completedCount', 
                'archivedCount'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error in uimmsPortal: ' . $e->getMessage());
            
            // Fallback counts
            $pendingCount = 0;
            $suspendedCount = 0;
            $completedCount = 0;
            $archivedCount = 0;
            
            return view('admin.uimms.portal', compact(
                'pendingCount', 
                'suspendedCount', 
                'completedCount', 
                'archivedCount'
            ));
        }
    }

    /**
     * Get memos by status for UIMMS
     */
    public function getMemosByStatus($status)
    {
        $userId = Auth::id();
        
        try {
            // Memos where user is participant, recipient, or specific-reply recipient
            $memos = EmailCampaign::with(['creator', 'currentAssignee', 'recipients.user', 'replies.user'])
                ->where(function($query) use ($userId) {
                    $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId);
                    })
                    ->orWhereHas('recipients', function($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId);
                    })
                    ->orWhereHas('replies', function($subQuery) use ($userId) {
                        $subQuery->where('reply_mode', 'specific')
                                ->whereJsonContains('specific_recipients', (string)$userId);
                    });
                })
                ->where(function($query) use ($status) {
                    if ($status === 'pending') {
                        // No status filter for pending
                    } else {
                        $query->where('memo_status', $status);
                    }
                })
                ->whereDoesntHave('bookmarkedBy', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->orderBy('updated_at', 'desc')
                ->get();

            // Pending only: also include memos the user created when there is activity so sender stays updated.
            // "Activity" = replies exist OR campaign was updated (assign, status change, etc.).
            // If you add new actions that should notify the creator, ensure they either create a reply or update the campaign (so updated_at changes).
            if ($status === 'pending') {
                $existingIds = $memos->pluck('id')->toArray();
                $creatorMemosWithActivity = EmailCampaign::with(['creator', 'currentAssignee', 'recipients.user', 'replies.user'])
                    ->where('created_by', $userId)
                    ->where(function ($q) {
                        $q->where('memo_status', 'pending')->orWhereNull('memo_status');
                    })
                    ->whereDoesntHave('bookmarkedBy', function($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->where(function ($q) {
                        $q->whereHas('replies')
                            ->orWhereRaw('updated_at > created_at');
                    })
                    ->whereNotIn('id', $existingIds)
                    ->orderBy('updated_at', 'desc')
                    ->get();
                $memos = $memos->merge($creatorMemosWithActivity)->unique('id')->sortByDesc('updated_at')->values();
            }

            // Transform the data to include UIMMS-specific information
            $memos = $memos->map(function($memo) use ($userId) {
                // Get active participants (only those with is_active_participant = true)
                $activeParticipants = $memo->activeParticipants;
                
                // Get last message
                $lastMessage = $memo->replies->sortByDesc('created_at')->first();
                
                // Check if memo is bookmarked by current user
                $isBookmarked = $memo->isBookmarkedBy($userId);
                
                // Get when the memo was received by the current user (recipient's created_at)
                $recipient = $memo->recipients->where('user_id', $userId)->first();
                $receivedAt = $recipient ? $recipient->created_at : $memo->created_at;

                // For pending memos only: compute "has new activity" (unread or new reply since last read)
                $hasNewActivity = true;
                if ($memo->memo_status === 'pending') {
                    $memoUpdated = $memo->updated_at ?? $memo->created_at;
                    $latestReplyAt = $memo->replies->isEmpty()
                        ? null
                        : $memo->replies->max('created_at');
                    $latestActivityAt = $latestReplyAt
                        ? (Carbon::parse($memoUpdated)->greaterThan(Carbon::parse($latestReplyAt)) ? Carbon::parse($memoUpdated) : Carbon::parse($latestReplyAt))
                        : Carbon::parse($memoUpdated);
                    $lastReadAt = $memo->getLastReadAtForUser($userId);
                    $hasNewActivity = $lastReadAt === null || $latestActivityAt->greaterThan($lastReadAt);
                }
                
                return [
                    'id' => $memo->id,
                    'subject' => $memo->subject,
                    'message' => $memo->message,
                    'created_at' => $memo->created_at,
                    'received_at' => $receivedAt, // When user received the memo
                    'updated_at' => $memo->updated_at,
                    'memo_status' => $memo->memo_status ?? 'pending',
                    'creator' => $memo->creator,
                    'current_assignee' => $memo->currentAssignee,
                    'active_participants' => $activeParticipants->values(),
                    'last_message' => $lastMessage,
                    'attachments' => $memo->attachments,
                    'is_bookmarked' => $isBookmarked,
                    'has_new_activity' => $hasNewActivity,
                ];
            });

            return response()->json($memos);
            
        } catch (\Exception $e) {
            \Log::error('Error in getMemosByStatus: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load memos'], 500);
        }
    }

    /**
     * Chat view for a specific memo
     */
    public function memoChat(EmailCampaign $memo)
    {
        $userId = Auth::id();
        
        try {
            // Check if user is an active participant, recipient, or the creator
            $isActiveParticipant = $memo->isActiveParticipant($userId);
            $isRecipient = $memo->recipients()->where('user_id', $userId)->exists();
            $isCreator = $memo->created_by === $userId;
            
            // If creator, check if memo is assigned to someone else
            $isAssignedToSomeoneElse = $isCreator && $memo->current_assignee_id && $memo->current_assignee_id != $userId;
            
            if (!$isActiveParticipant && !$isRecipient && !$isCreator) {
                abort(403, 'You are not a participant in this memo conversation.');
            }
            
            // If user is a recipient but not an active participant, they can view but not participate
            // Creator can participate only if memo is not assigned to someone else
            $canParticipate = $isActiveParticipant || ($isCreator && !$isAssignedToSomeoneElse);

            $memo->load([
                'creator', 
                'currentAssignee', 
                'recipients.user',
                'activeParticipants.user',
                'replies.user'
            ]);

            // Set up active participants for the view (only active participants)
            $memo->active_participants = $memo->activeParticipants->map(function($recipient) {
                return [
                    'user' => $recipient->user,
                    'is_active_participant' => true
                ];
            });

            // Filter replies based on user visibility
            $memo->replies = $memo->replies->filter(function ($reply) use ($userId, $isActiveParticipant, $memo) {
                // User can always see their own messages
                if ($reply->user_id === $userId) {
                    return true;
                }
                
                // If user is not an active participant, only show messages from when they were active
                if (!$isActiveParticipant) {
                    // Check if this message was sent before the user became inactive
                    $userRecipient = $memo->recipients->where('user_id', $userId)->first();
                    if ($userRecipient && $userRecipient->last_activity_at) {
                        // Only show messages sent before the user's last activity
                        return $reply->created_at <= $userRecipient->last_activity_at;
                    }
                    // If no last_activity_at, show all messages (backward compatibility)
                    return true;
                }
                
                // For active participants, show all messages based on reply mode
                // For 'all' replies, everyone can see them
                if ($reply->reply_mode === 'all') {
                    return true;
                }
                
                // For 'specific' replies, only the sender and specific recipients can see them
                if ($reply->reply_mode === 'specific' && $reply->specific_recipients) {
                    return in_array($userId, $reply->specific_recipients);
                }
                
                // Default: show the message (fallback for old messages without reply_mode)
                return true;
            });

            // Record that this user has "read" this memo (for Active Chat vs Read split on portal)
            $memo->recordLastReadBy($userId);

            // Get all users for assignment dropdown
            $users = User::where('is_approve', true)
                ->where('id', '!=', $userId)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            return view('admin.uimms.chat', compact('memo', 'users', 'canParticipate', 'isAssignedToSomeoneElse'));
            
        } catch (\Exception $e) {
            \Log::error('Error in memoChat: ' . $e->getMessage());
            abort(500, 'Error loading memo chat.');
        }
    }

    /**
     * Send a chat message in memo
     */
    public function sendChatMessage(Request $request, EmailCampaign $memo)
    {
        $userId = Auth::id();
        
        // Check if memo is completed or archived - these are read-only
        if (in_array($memo->memo_status, ['completed', 'archived'])) {
            abort(403, 'This memo is ' . $memo->memo_status . ' and cannot receive new messages.');
        }
        
        // Check if user is an active participant or the creator (only these can send messages)
        $isActiveParticipant = $memo->isActiveParticipant($userId);
        $isCreator = $memo->created_by === $userId;
        
        // If creator, check if memo is assigned to someone else
        $isAssignedToSomeoneElse = $isCreator && $memo->current_assignee_id && $memo->current_assignee_id != $userId;
        
        if ($isAssignedToSomeoneElse) {
            return response()->json([
                'success' => false,
                'message' => 'This memo has been assigned to another user. You cannot send messages until it is reassigned to you.'
            ], 403);
        }
        
        if (!$isActiveParticipant && !$isCreator) {
            abort(403, 'You are not an active participant in this memo conversation.');
        }

        $request->validate([
            'message' => 'required|string|max:5000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,txt,jpg,jpeg,png,gif',
            'reply_mode' => 'required|in:all,specific',
            'specific_recipients' => 'nullable|string',
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('memo-replies', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        // Process specific recipients
        $specificRecipients = null;
        if ($request->reply_mode === 'specific' && $request->specific_recipients) {
            $specificRecipients = explode(',', $request->specific_recipients);
            $specificRecipients = array_filter(array_map('trim', $specificRecipients));
        }

        $reply = MemoReply::create([
            'campaign_id' => $memo->id,
            'user_id' => $userId,
            'message' => $request->message,
            'attachments' => $attachments,
            'reply_mode' => $request->reply_mode,
            'specific_recipients' => $specificRecipients,
        ]);

        // Update last activity for all active participants
        $memo->activeParticipants()->update(['last_activity_at' => now()]);

        // Create notifications based on reply mode
        if ($request->reply_mode === 'all') {
            // Notify all other active participants
            $otherParticipants = $memo->activeParticipants()
                ->where('user_id', '!=', $userId)
                ->with('user')
                ->get();
        } else {
            // Notify only specific recipients
            $otherParticipants = $memo->activeParticipants()
                ->where('user_id', '!=', $userId)
                ->whereIn('user_id', $specificRecipients)
                ->with('user')
                ->get();
        }

        foreach ($otherParticipants as $participant) {
            $notificationMessage = $request->reply_mode === 'specific' 
                ? Auth::user()->first_name . ' sent you a direct message in: ' . $memo->subject
                : Auth::user()->first_name . ' sent a message in: ' . $memo->subject;
                
            Notification::create([
                'user_id' => $participant->user_id,
                'type' => 'memo_message',
                'title' => $request->reply_mode === 'specific' ? 'Direct Message in Memo' : 'New Message in Memo',
                'message' => $notificationMessage,
                'url' => route('dashboard.uimms.chat', $memo->id),
                'data' => [
                    'memo_id' => $memo->id,
                    'sender_name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $reply->load('user'),
        ]);
    }

    /**
     * Assign memo to another user
     */
    public function assignMemo(Request $request, EmailCampaign $memo)
    {
        $userId = Auth::id();
        
        // Check if user is an active participant, recipient, or the creator
        $isActiveParticipant = $memo->isActiveParticipant($userId);
        $isRecipient = $memo->recipients()->where('user_id', $userId)->exists();
        $isCreator = $memo->created_by === $userId;
        $isCurrentAssignee = $memo->current_assignee_id == $userId;
        
        // Only current assignee or active participants can assign
        $canManageMemo = $isCurrentAssignee || $isActiveParticipant;
        
        if (!$canManageMemo) {
            abort(403, 'Only the current assignee or active participants can manage this memo.');
        }

        $request->validate([
            'assignee_ids' => 'required|array|min:1',
            'assignee_ids.*' => 'required|exists:users,id',
            'office' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        $assigneeIds = $request->assignee_ids;
        $assignees = User::whereIn('id', $assigneeIds)->get();
        
        if ($assignees->count() !== count($assigneeIds)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more selected users were not found.',
            ], 422);
        }
        
        // Assign the memo to multiple users
        $memo->assignToMultiple($assigneeIds, $userId, $request->office);

        // Build assignment message with all assignees - format based on count
        $assigneeNamesList = $assignees->map(function($assignee) {
            return $assignee->first_name . ' ' . $assignee->last_name;
        })->toArray();
        
        $assigneeNames = '';
        $count = count($assigneeNamesList);
        
        if ($count === 1) {
            $assigneeNames = $assigneeNamesList[0];
        } elseif ($count === 2) {
            $assigneeNames = $assigneeNamesList[0] . ' and ' . $assigneeNamesList[1];
        } else {
            // For 3 or more: "A, B, and C"
            $lastName = array_pop($assigneeNamesList);
            $assigneeNames = implode(', ', $assigneeNamesList) . ', and ' . $lastName;
        }
        
        $assignmentMessage = "<em>📋 Memo Assigned by " . Auth::user()->first_name . " " . Auth::user()->last_name . " to " . $assigneeNames . "</em>";
        if ($request->message) {
            $assignmentMessage .= "<div style='margin: 8px 0; border-top: 1px solid rgba(0,0,0,0.1); width: 100%;'></div>" . nl2br(e($request->message));
        }
        
        MemoReply::create([
            'campaign_id' => $memo->id,
            'user_id' => $userId,
            'message' => $assignmentMessage,
            'attachments' => [],
        ]);

        // Create notifications for all new assignees
        $assignerName = Auth::user()->first_name . ' ' . Auth::user()->last_name;
        foreach ($assignees as $assignee) {
            Notification::create([
                'user_id' => $assignee->id,
                'type' => 'memo_assigned',
                'title' => 'Memo Assigned to You',
                'message' => $assignerName . ' assigned a memo to you: ' . $memo->subject,
                'url' => route('dashboard.uimms.chat', $memo->id),
                'data' => [
                    'memo_id' => $memo->id,
                    'assigned_by' => $assignerName,
                ]
            ]);
        }

        // Send email notifications
        try {
            // Send success email to assigner with primary assignee (for email template compatibility)
            Mail::to(Auth::user()->email)->send(new \App\Mail\MemoAssignmentSuccess(
                $memo, 
                Auth::user(), 
                $assignees->first(), // Primary assignee for email template compatibility
                $request->office
            ));

            // Send notification email to each assignee
            foreach ($assignees as $assignee) {
                Mail::to($assignee->email)->send(new \App\Mail\MemoAssignedNotification(
                    $memo, 
                    Auth::user(), 
                    $assignee, 
                    $request->office
                ));
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the assignment
            \Log::error('Failed to send memo assignment emails: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Memo assigned successfully to ' . $assignees->count() . ' user(s)',
            'assignees' => $assignees,
        ]);
    }

    /**
     * Update memo status (complete, suspend, archive)
     */
    public function updateMemoStatus(Request $request, EmailCampaign $memo)
    {
        $userId = Auth::id();
        
        // Check if memo is archived - archived memos cannot have status changes
        if ($memo->memo_status === 'archived') {
            abort(403, 'This memo is archived and cannot have its status changed.');
        }
        
        // Check if user is an active participant, recipient, or the creator
        $isActiveParticipant = $memo->isActiveParticipant($userId);
        $isRecipient = $memo->recipients()->where('user_id', $userId)->exists();
        $isCreator = $memo->created_by === $userId;
        $isCurrentAssignee = $memo->current_assignee_id == $userId;
        
        // Only current assignee or active participants can manage memo status
        $canManageMemo = $isCurrentAssignee || $isActiveParticipant;
        
        if (!$canManageMemo) {
            abort(403, 'Only the current assignee or active participants can manage this memo.');
        }

        $request->validate([
            'status' => 'required|in:completed,suspended,unsuspended,archived',
            'reason' => 'nullable|string|max:1000',
        ]);

        switch ($request->status) {
            case 'completed':
                $memo->markAsCompleted($userId);
                break;
            case 'suspended':
                $memo->markAsSuspended($userId, $request->reason);
                break;
            case 'unsuspended':
                // Check if the current user can unsuspend this memo
                if (!$memo->canUnsuspend($userId)) {
                    abort(403, 'Only the user who suspended this memo can unsuspend it.');
                }
                $memo->markAsUnsuspended($userId);
                break;
            case 'archived':
                $memo->markAsArchived($userId);
                break;
        }

        // Send a system message about status change
        $statusMessages = [
            'completed' => '✅ <em>Memo marked as completed</em>',
            'suspended' => '⏸️ <em>Memo suspended</em>' . ($request->reason ? "\n\nReason: " . $request->reason : ''),
            'unsuspended' => '▶️ <em>Memo unsuspended - conversation resumed</em>',
            'archived' => '📦 <em>Memo archived</em>',
        ];

        MemoReply::create([
            'campaign_id' => $memo->id,
            'user_id' => $userId,
            'message' => $statusMessages[$request->status],
            'attachments' => [],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Memo status updated successfully',
            'new_status' => $request->status,
        ]);
    }

    /**
     * Get chat messages for a memo (AJAX)
     */
    public function getChatMessages(EmailCampaign $memo)
    {
        $userId = Auth::id();
        
        try {
            // Check if user is a recipient or the creator
            $isRecipient = $memo->recipients()->where('user_id', $userId)->exists();
            $isCreator = $memo->created_by === $userId;
            $isActiveParticipant = $memo->isActiveParticipant($userId);
            
            if (!$isRecipient && !$isCreator) {
                abort(403, 'You are not a participant in this memo conversation.');
            }

            // Get all messages first
            $allMessages = $memo->replies()->with('user')->orderBy('created_at', 'asc')->get();

            // Filter messages based on user visibility
            $filteredMessages = $allMessages->filter(function ($reply) use ($userId, $isActiveParticipant, $memo) {
                // User can always see their own messages
                if ($reply->user_id === $userId) {
                    return true;
                }
                
                // If user is not an active participant, only show messages from when they were active
                if (!$isActiveParticipant) {
                    // Check if this message was sent before the user became inactive
                    $userRecipient = $memo->recipients->where('user_id', $userId)->first();
                    if ($userRecipient && $userRecipient->last_activity_at) {
                        // Only show messages sent before the user's last activity
                        return $reply->created_at <= $userRecipient->last_activity_at;
                    }
                    // If no last_activity_at, show all messages (backward compatibility)
                    return true;
                }
                
                // For active participants, show all messages based on reply mode
                // For 'all' replies, everyone can see them
                if ($reply->reply_mode === 'all') {
                    return true;
                }
                
                // For 'specific' replies, only the sender and specific recipients can see them
                if ($reply->reply_mode === 'specific' && $reply->specific_recipients) {
                    // Handle both string and array formats
                    $specificRecipients = $reply->specific_recipients;
                    if (is_string($specificRecipients)) {
                        $specificRecipients = json_decode($specificRecipients, true) ?: explode(',', $specificRecipients);
                    }
                    return in_array($userId, $specificRecipients);
                }
                
                // Default: show the message (fallback for old messages without reply_mode)
                return true;
            });

            return response()->json($filteredMessages->values());
            
        } catch (\Exception $e) {
            \Log::error('Error in getChatMessages: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load messages'], 500);
        }
    }

    public function downloadUimmsMemoAttachment(EmailCampaign $memo, $index)
    {
        $userId = Auth::id();
        
        // Check if user is a recipient of this memo
        $isRecipient = $memo->recipients()->where('user_id', $userId)->exists();
        $isCreator = $memo->created_by === $userId;
        
        if (!$isRecipient && !$isCreator) {
            abort(403, 'Unauthorized access to this attachment.');
        }
        
        $attachments = $memo->attachments;
        
        // Check if the attachment index is valid
        if (!isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$index];
        $filePath = storage_path('app/public/' . $attachment['path']);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found on server.');
        }
        
        // Return file download response
        return response()->download($filePath, $attachment['name']);
    }

    public function viewUimmsMemoAttachment(EmailCampaign $memo, $index)
    {
        $userId = Auth::id();
        
        // Check if user is a recipient of this memo
        $isRecipient = $memo->recipients()->where('user_id', $userId)->exists();
        $isCreator = $memo->created_by === $userId;
        
        if (!$isRecipient && !$isCreator) {
            abort(403, 'Unauthorized access to this attachment.');
        }
        
        $attachments = $memo->attachments;
        
        // Check if the attachment index is valid
        if (!isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$index];
        $filePath = storage_path('app/public/' . $attachment['path']);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found on server.');
        }
        
        // Return file view response
        return response()->file($filePath);
    }

    public function downloadUimmsChatAttachment(MemoReply $reply, $index)
    {
        $userId = Auth::id();
        
        // Check if user has access to this chat reply
        $memo = $reply->campaign;
        $isRecipient = $memo->recipients()->where('user_id', $userId)->exists();
        $isCreator = $memo->created_by === $userId;
        $isReplyAuthor = $reply->user_id === $userId;
        
        if (!$isRecipient && !$isCreator && !$isReplyAuthor) {
            abort(403, 'Unauthorized access to this attachment.');
        }
        
        $attachments = $reply->attachments;
        
        // Check if the attachment index is valid
        if (!isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$index];
        $filePath = storage_path('app/public/' . $attachment['path']);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found on server.');
        }
        
        // Return file download response
        return response()->download($filePath, $attachment['name']);
    }

    public function viewUimmsChatAttachment(MemoReply $reply, $index)
    {
        $userId = Auth::id();
        
        // Check if user has access to this chat reply
        $memo = $reply->campaign;
        $isRecipient = $memo->recipients()->where('user_id', $userId)->exists();
        $isCreator = $memo->created_by === $userId;
        $isReplyAuthor = $reply->user_id === $userId;
        
        if (!$isRecipient && !$isCreator && !$isReplyAuthor) {
            abort(403, 'Unauthorized access to this attachment.');
        }
        
        $attachments = $reply->attachments;
        
        // Check if the attachment index is valid
        if (!isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$index];
        $filePath = storage_path('app/public/' . $attachment['path']);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found on server.');
        }
        
        // Return file view response
        return response()->file($filePath);
    }

    /**
     * Bulk archive all completed memos
     */
    public function bulkArchiveCompleted(Request $request)
    {
        $userId = Auth::id();
        
        try {
            // Get all completed memos where user is a participant
            $completedMemos = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'completed')->get();
            
            $archivedCount = 0;
            
            foreach ($completedMemos as $memo) {
                // Update memo status to archived
                $memo->update(['memo_status' => 'archived']);
                
                // Add to workflow history
                $workflowHistory = $memo->workflow_history ?? [];
                $workflowHistory[] = [
                    'action' => 'bulk_archived',
                    'user_id' => $userId,
                    'timestamp' => now()->toISOString(),
                    'status' => 'archived',
                    'reason' => 'Bulk archived from completed status'
                ];
                $memo->update(['workflow_history' => $workflowHistory]);
                
                $archivedCount++;
            }
            
            // Get updated counts
            $completedCount = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'completed')->count();
            
            $archivedCountTotal = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'archived')->count();
            
            return response()->json([
                'success' => true,
                'archived_count' => $archivedCount,
                'counts' => [
                    'completed' => $completedCount,
                    'archived' => $archivedCountTotal
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in bulkArchiveCompleted: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to archive completed memos'], 500);
        }
    }

    /**
     * Bulk archive selected completed memos
     */
    public function bulkArchiveSelected(Request $request)
    {
        $userId = Auth::id();
        
        $request->validate([
            'memo_ids' => 'required|array',
            'memo_ids.*' => 'integer'
        ]);
        
        try {
            $memoIds = $request->memo_ids;
            
            // Debug: Log the memo IDs being processed
            \Log::info('Processing bulk archive for memos:', [
                'user_id' => $userId,
                'memo_ids' => $memoIds
            ]);
            
            // Get selected memos where user is a participant and status is completed
            $selectedMemos = EmailCampaign::whereIn('id', $memoIds)
                ->where('memo_status', 'completed')
                ->where(function($query) use ($userId) {
                    $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId);
                    })->orWhereHas('recipients', function($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId);
                    });
                })->get();
            
            // Debug: Log how many memos were found
            \Log::info('Found memos to archive:', [
                'count' => $selectedMemos->count(),
                'memo_ids_found' => $selectedMemos->pluck('id')->toArray()
            ]);
            
            $archivedCount = 0;
            
            foreach ($selectedMemos as $memo) {
                // Update memo status to archived
                $memo->update(['memo_status' => 'archived']);
                
                // Add to workflow history
                $workflowHistory = $memo->workflow_history ?? [];
                $workflowHistory[] = [
                    'action' => 'bulk_archived_selected',
                    'user_id' => $userId,
                    'timestamp' => now()->toISOString(),
                    'status' => 'archived',
                    'reason' => 'Bulk archived selected memos from completed status'
                ];
                $memo->update(['workflow_history' => $workflowHistory]);
                
                $archivedCount++;
            }
            
            // Get updated counts
            $completedCount = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'completed')->count();
            
            $archivedCountTotal = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'archived')->count();
            
            return response()->json([
                'success' => true,
                'archived_count' => $archivedCount,
                'counts' => [
                    'completed' => $completedCount,
                    'archived' => $archivedCountTotal
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in bulkArchiveSelected: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to archive selected memos'], 500);
        }
    }

    /**
     * Bulk unarchive selected memos
     */
    public function bulkUnarchiveSelected(Request $request)
    {
        $userId = Auth::id();
        
        $request->validate([
            'memo_ids' => 'required|string'
        ]);
        
        try {
            $memoIds = json_decode($request->memo_ids, true);
            
            // Debug: Log the memo IDs being processed
            \Log::info('Processing bulk unarchive for memos:', [
                'user_id' => $userId,
                'memo_ids' => $memoIds
            ]);
            
            // Get selected memos where user is a participant and status is archived
            $selectedMemos = EmailCampaign::whereIn('id', $memoIds)
                ->where('memo_status', 'archived')
                ->where(function($query) use ($userId) {
                    $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId);
                    })->orWhereHas('recipients', function($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId);
                    });
                })->get();
            
            // Debug: Log how many memos were found
            \Log::info('Found memos to unarchive:', [
                'count' => $selectedMemos->count(),
                'memo_ids_found' => $selectedMemos->pluck('id')->toArray()
            ]);
            
            $unarchivedCount = 0;
            
            foreach ($selectedMemos as $memo) {
                // Update memo status to completed (unarchive)
                $memo->update(['memo_status' => 'completed']);
                
                // Add to workflow history
                $workflowHistory = $memo->workflow_history ?? [];
                $workflowHistory[] = [
                    'action' => 'bulk_unarchived',
                    'user_id' => $userId,
                    'timestamp' => now()->toISOString(),
                    'status' => 'completed',
                    'reason' => 'Bulk unarchived from archived status'
                ];
                $memo->update(['workflow_history' => $workflowHistory]);
                
                $unarchivedCount++;
            }
            
            // Get updated counts
            $completedCount = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'completed')->count();
            
            $archivedCountTotal = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'archived')->count();
            
            return response()->json([
                'success' => true,
                'unarchived_count' => $unarchivedCount,
                'counts' => [
                    'completed' => $completedCount,
                    'archived' => $archivedCountTotal
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in bulkUnarchiveSelected: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to unarchive selected memos'], 500);
        }
    }

    /**
     * Bulk reactivate selected completed memos (move from completed to pending)
     */
    public function bulkReactivateSelected(Request $request)
    {
        $userId = Auth::id();
        
        $request->validate([
            'memo_ids' => 'required|string'
        ]);
        
        try {
            $memoIds = json_decode($request->memo_ids, true);
            
            // Debug: Log the memo IDs being processed
            \Log::info('Processing bulk reactivate for memos:', [
                'user_id' => $userId,
                'memo_ids' => $memoIds
            ]);
            
            // Get selected memos where user is a participant and status is completed
            $selectedMemos = EmailCampaign::whereIn('id', $memoIds)
                ->where('memo_status', 'completed')
                ->where(function($query) use ($userId) {
                    $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId);
                    })->orWhereHas('recipients', function($subQuery) use ($userId) {
                        $subQuery->where('user_id', $userId);
                    });
                })->get();
            
            // Debug: Log how many memos were found
            \Log::info('Found memos to reactivate:', [
                'count' => $selectedMemos->count(),
                'memo_ids_found' => $selectedMemos->pluck('id')->toArray()
            ]);
            
            $reactivatedCount = 0;
            
            foreach ($selectedMemos as $memo) {
                $memo->update(['memo_status' => 'pending']);
                
                // Add to workflow history
                $workflowHistory = $memo->workflow_history ?? [];
                $workflowHistory[] = [
                    'action' => 'bulk_reactivated_selected',
                    'user_id' => $userId,
                    'timestamp' => now()->toISOString(),
                    'status' => 'pending',
                    'reason' => 'Bulk reactivated selected memos from completed status'
                ];
                $memo->update(['workflow_history' => $workflowHistory]);
                
                $reactivatedCount++;
            }
            
            // Get updated counts
            $pendingCount = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'pending')->count();
            
            $completedCount = EmailCampaign::where(function($query) use ($userId) {
                $query->whereHas('activeParticipants', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                })->orWhereHas('recipients', function($subQuery) use ($userId) {
                    $subQuery->where('user_id', $userId);
                });
            })->where('memo_status', 'completed')->count();
            
            return response()->json([
                'success' => true,
                'reactivated_count' => $reactivatedCount,
                'counts' => [
                    'pending' => $pendingCount,
                    'completed' => $completedCount
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in bulkReactivateSelected: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to reactivate selected memos'], 500);
        }
    }

    /**
     * Keep in View page - Show all bookmarked memos
     */
    public function keepInView()
    {
        $userId = Auth::id();
        
        try {
            $bookmarkedCount = Auth::user()->bookmarkedMemos()->count();
            
            return view('admin.uimms.keep-in-view', compact('bookmarkedCount'));
        } catch (\Exception $e) {
            \Log::error('Error in keepInView: ' . $e->getMessage());
            $bookmarkedCount = 0;
            return view('admin.uimms.keep-in-view', compact('bookmarkedCount'));
        }
    }

    /**
     * Get bookmarked memos for the current user
     */
    public function getBookmarkedMemos()
    {
        $userId = Auth::id();
        
        try {
            $memos = Auth::user()->bookmarkedMemos()
                ->with(['creator', 'currentAssignee', 'recipients.user', 'replies.user'])
                ->orderBy('memo_user_bookmarks.created_at', 'desc')
                ->get();

            // Transform the data to include UIMMS-specific information
            $memos = $memos->map(function($memo) use ($userId) {
                // Get active participants (only those with is_active_participant = true)
                $activeParticipants = $memo->activeParticipants;
                
                // Get last message
                $lastMessage = $memo->replies->sortByDesc('created_at')->first();
                
                // Get when the memo was received by the current user (recipient's created_at)
                $recipient = $memo->recipients->where('user_id', $userId)->first();
                $receivedAt = $recipient ? $recipient->created_at : $memo->created_at;
                
                return [
                    'id' => $memo->id,
                    'subject' => $memo->subject,
                    'message' => $memo->message,
                    'created_at' => $memo->created_at,
                    'received_at' => $receivedAt, // When user received the memo
                    'updated_at' => $memo->updated_at,
                    'memo_status' => $memo->memo_status ?? 'pending',
                    'creator' => $memo->creator,
                    'current_assignee' => $memo->currentAssignee,
                    'active_participants' => $activeParticipants->values(),
                    'last_message' => $lastMessage,
                    'attachments' => $memo->attachments,
                    'is_bookmarked' => true, // Always true for bookmarked memos
                ];
            });

            return response()->json($memos);
            
        } catch (\Exception $e) {
            \Log::error('Error in getBookmarkedMemos: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load bookmarked memos'], 500);
        }
    }

    /**
     * Toggle bookmark status for a memo
     */
    public function toggleBookmark(EmailCampaign $memo)
    {
        $userId = Auth::id();
        
        try {
            $user = Auth::user();
            $isBookmarked = $memo->isBookmarkedBy($userId);
            
            if ($isBookmarked) {
                // Remove bookmark
                $user->bookmarkedMemos()->detach($memo->id);
                $message = 'Memo removed from Keep in View';
            } else {
                // Add bookmark
                $user->bookmarkedMemos()->attach($memo->id);
                $message = 'Memo added to Keep in View';
            }
            
            return response()->json([
                'success' => true,
                'is_bookmarked' => !$isBookmarked,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in toggleBookmark: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bookmark status'
            ], 500);
        }
    }

    /**
     * Send urgency alert email for a pending memo
     */
    public function sendUrgencyAlert(EmailCampaign $memo)
    {
        $userId = Auth::id();
        $sender = Auth::user();
        
        try {
            // Check if memo is pending (uses model rule: pending includes null in DB)
            if (!$memo->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Urgency alerts can only be sent for pending memos.'
                ], 400);
            }
            
            // Check if user is a participant in this memo
            $isActiveParticipant = $memo->isActiveParticipant($userId);
            $isRecipient = $memo->recipients()->where('user_id', $userId)->exists();
            $isCreator = $memo->created_by === $userId;
            
            if (!$isActiveParticipant && !$isRecipient && !$isCreator) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant in this memo.'
                ], 403);
            }
            
            // Get all active participants and recipients
            $recipients = collect();
            
            // Add active participants
            $activeParticipants = $memo->activeParticipants()->with('user')->get();
            foreach ($activeParticipants as $participant) {
                if ($participant->user && $participant->user->id !== $userId) {
                    $recipients->push($participant->user);
                }
            }
            
            // Add other recipients
            $otherRecipients = $memo->recipients()->with('user')->get();
            foreach ($otherRecipients as $recipient) {
                if ($recipient->user && $recipient->user->id !== $userId && !$recipients->contains('id', $recipient->user->id)) {
                    $recipients->push($recipient->user);
                }
            }
            
            // Add creator if different from sender
            if ($memo->creator && $memo->creator->id !== $userId && !$recipients->contains('id', $memo->creator->id)) {
                $recipients->push($memo->creator);
            }
            
            // Send email to each recipient
            $sentCount = 0;
            foreach ($recipients as $recipient) {
                try {
                    Mail::to($recipient->email)->send(new \App\Mail\MemoUrgencyReminder(
                        $memo,
                        $sender,
                        $recipient
                    ));
                    $sentCount++;
                } catch (\Exception $e) {
                    \Log::error('Failed to send urgency alert to ' . $recipient->email . ': ' . $e->getMessage());
                }
            }
            
            if ($sentCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No recipients found to send the urgency alert to.'
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => "Urgency alert sent successfully to {$sentCount} recipient(s).",
                'sent_count' => $sentCount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in sendUrgencyAlert: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send urgency alert. Please try again.'
            ], 500);
        }
    }
}
