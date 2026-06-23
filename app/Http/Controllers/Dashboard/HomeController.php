<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Mail\Approval;
use App\Models\Academic;
use App\Models\Department;
use App\Models\File;
use App\Models\Folder;
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
use Barryvdh\DomPDF\Facade\Pdf;

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
        // Each user (including admins) only sees their own uploads on this page.
        $user = Auth::user();
        $userId = $user->id;

        $exams = Exam::where('user_id', $userId)
            ->whereDoesntHave('folders')
            ->orderBy('created_at', 'desc')
            ->get();

        $files = File::where('user_id', $userId)
            ->whereDoesntHave('folders')
            ->orderBy('created_at', 'desc')
            ->get();

        $folders = Folder::where('user_id', $userId)
            ->withCount(['files', 'exams'])
            ->orderBy('created_at', 'desc')
            ->get();

        $sharedFolders = Folder::sharedListingFor($user);

        return view('admin.documents', compact('exams', 'files', 'folders', 'sharedFolders'));
    }

    public function uploadedDocument(){
        $user = Auth::user();
        $exams = Exam::where('user_id', $user->id)
            ->whereDoesntHave('folders')
            ->orderBy('created_at', 'desc')
            ->get();
        $folders = Folder::where('user_id', $user->id)
            ->withCount(['files', 'exams'])
            ->orderBy('created_at', 'desc')
            ->get();
        $sharedFolders = Folder::sharedListingFor($user);
        return view('admin.uploaded_documents', compact('exams', 'folders', 'sharedFolders'));
    }

    public function allUploadedDocument(){
        // Only show user's own exams (no approval system means users manage their own content)
        $user = Auth::user();
        $exams = Exam::where('user_id', $user->id)
            ->whereDoesntHave('folders')
            ->orderBy('created_at', 'desc')
            ->get();
        $folders = Folder::where('user_id', $user->id)
            ->withCount(['files', 'exams'])
            ->orderBy('created_at', 'desc')
            ->get();
        $sharedFolders = Folder::sharedListingFor($user);
        return view('admin.all_uploaded_documents', compact('exams', 'folders', 'sharedFolders'));
    }

    // Unified Exams view (no more pending/approved separation)
    public function myExams(){
        $exams = Exam::where('user_id', Auth::user()->id)->get();
        return view('admin.my_exams',compact('exams'));
    }

    public function allExams(){
        // Only show user's own exams (no approval system means users manage their own content)
        $user = Auth::user();
        $exams = Exam::where('user_id', $user->id)
            ->whereDoesntHave('folders')
            ->orderBy('created_at', 'desc')
            ->get();
        $folders = Folder::where('user_id', $user->id)
            ->withCount(['files', 'exams'])
            ->orderBy('created_at', 'desc')
            ->get();
        $sharedFolders = Folder::sharedListingFor($user);
        return view('admin.all_exams', compact('exams', 'folders', 'sharedFolders'));
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
        $userId = Auth::id();

        // Same modern pattern as getNotifications — keep recently-read memos
        // visible so "Mark all as read" doesn't appear to wipe the tray.
        $recentMemos = EmailCampaignRecipient::with(['campaign.creator'])
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->where('is_read', false)
                  ->orWhere('read_at', '>=', now()->subDays(7));
            })
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at','desc')
            ->limit(15)
            ->get();

        $memos = $recentMemos->map(function ($rm) use ($userId) {
            $campaign = $rm->campaign;
            $messagePreview = strip_tags($campaign->message ?? '');
            $messagePreview = \Str::limit($messagePreview, 100);

            // Open the memo inside the UIMMS portal chat — that particular memo,
            // where the recipient can read it and minute / archive / suspend /
            // take action. uimms.chat is keyed by the memo/campaign id.
            $chatUrl = route('dashboard.uimms.chat', $rm->comm_campaign_id);

            // Contextual label + action so this SINGLE memo card tells the user
            // what they have to do — this replaces the separate "Memo forwarded to
            // you" / "awaiting your forward" / "approval needed" Notification rows
            // we used to create alongside the memo (the duplicate-tray-card bug).
            [$contextLabel, $contextStyle, $primaryAction] =
                $this->memoCardContext($rm, $campaign, $userId, $chatUrl);

            return [
                'id'             => $rm->id,
                'subject'        => \Str::limit($campaign->subject ?? 'Memo', 40),
                'message'        => $messagePreview,
                'context_label'  => $contextLabel,
                'context_style'  => $contextStyle,
                'created_at'     => $rm->created_at->diffForHumans(),
                'created_at_iso' => $rm->created_at->toIso8601String(),
                'bucket'         => $this->dateBucket($rm->created_at),
                'is_read'        => (bool) $rm->is_read,
                'url'            => $chatUrl,
                'actor'          => $this->actorPayload($campaign?->creator),
                'actions'        => [$primaryAction],
            ];
        });

        return response()->json([
            'memos' => $memos
        ]);
    }

    /**
     * Derive the contextual eyebrow label, colour style, and primary action for a
     * memo's tray card from the recipient's role + the memo's workflow state.
     * Returns [label, style, action]. `style` is one of memo|action|urgent and
     * maps to the eyebrow colour in the notification tray.
     */
    protected function memoCardContext(EmailCampaignRecipient $rm, ?EmailCampaign $campaign, int $userId, string $chatUrl): array
    {
        $openAction = ['label' => 'Open memo', 'url' => $chatUrl, 'style' => 'primary'];

        if (!$campaign) {
            return ['New memo', 'memo', $openAction];
        }

        // A form-linked memo that hasn't been unlocked yet and is sitting in THIS
        // user's court is an action item: they must approve to unlock the form.
        $approvalPending = method_exists($campaign, 'hasLinkedForms')
            && $campaign->hasLinkedForms()
            && method_exists($campaign, 'isFormUnlocked')
            && ! $campaign->isFormUnlocked()
            && (int) $campaign->current_assignee_id === (int) $userId;

        // Through intermediary — still holding the memo, needs to forward it.
        if ($rm->recipient_role === 'through') {
            if ($campaign->through_status === 'pending') {
                return ['Awaiting your forward', 'action',
                    ['label' => 'Open & forward', 'url' => $chatUrl, 'style' => 'primary']];
            }
            return ['Routed through you', 'memo', $openAction];
        }

        if ($approvalPending) {
            return ['Approval needed', 'urgent',
                ['label' => 'Open to approve', 'url' => $chatUrl, 'style' => 'success']];
        }

        if ($rm->recipient_role === 'cc') {
            return ['Cc’d to you', 'memo', $openAction];
        }

        // Reached you via a Through intermediary.
        if (!empty($campaign->through_user_id)) {
            return ['Forwarded to you', 'memo', $openAction];
        }

        return ['New memo', 'memo', $openAction];
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
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,txt,xls,xlsx,csv,ppt,pptx,jpg,jpeg,png,gif',
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
            
            // Open the memo inside the UIMMS portal chat, where the user can read
            // the memo content and minute / take action on it — the same page the
            // chat-message and assignment notifications point to.
            $memoUrl = route('dashboard.uimms.chat', $campaign->id);

            Notification::createMemoReplyNotification(
                $campaignRecipient->user_id,
                $replyAuthor,
                $campaign->subject,
                $memoUrl
            );
        }

        // Also notify the memo creator if they're not already a recipient
        if ($campaign->created_by !== Auth::id()) {
            $creator = User::find($campaign->created_by);
            if ($creator) {
                Notification::createMemoReplyNotification(
                    $campaign->created_by,
                    $replyAuthor,
                    $campaign->subject,
                    route('dashboard.uimms.chat', $campaign->id)
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
        $user = Auth::user();
        $lastSeen = $user->last_tray_seen_at; // Capture BEFORE we touch it.

        // Modern tray behavior (Slack / Linear / GitHub):
        // - Show ALL unread, plus recently-read so "Mark as read" doesn't
        //   wipe the tray. Read items lose the unread highlight but remain
        //   visible until 7 days have passed or the user explicitly Clears.
        $notifications = Notification::forUser($user->id)
            ->with('actor:id,first_name,last_name,profile_picture')
            ->where(function ($q) {
                $q->where('is_read', false)
                  ->orWhere('read_at', '>=', now()->subDays(7));
            })
            ->orderBy('is_read', 'asc')          // unread first
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get()
            ->map(fn ($n) => [
                'id'              => $n->id,
                'type'            => $n->type,
                'category'        => $n->resolved_category,
                'title'           => $n->title,
                'message'         => $n->message,
                'url'             => $n->url,
                'is_read'         => (bool) $n->is_read,
                'time_ago'        => $n->time_ago,
                'created_at_iso'  => $n->created_at->toIso8601String(),
                'bucket'          => $this->dateBucket($n->created_at),
                'is_new_since_seen' => $lastSeen ? $n->created_at->gt($lastSeen) : true,
                'actor'           => $this->actorPayload($n->actor),
                'actions'         => $this->buildActions($n),
            ]);

        // Stamp the "last seen" cursor AFTER we read it, so the next call can
        // diff against it. We only stamp on the JSON fetch (tray-open), not on
        // the lightweight /check endpoint.
        $user->forceFill(['last_tray_seen_at' => now()])->save();

        return response()->json([
            'notifications' => $notifications,
            'last_seen_at'  => optional($lastSeen)->toIso8601String(),
        ]);
    }

    /**
     * Map a Notification to a small set of inline actions for the tray UI.
     * Each row in the tray will show 1–2 buttons users can act on without
     * leaving the tray (like Linear / GitHub).
     */
    protected function buildActions(Notification $n): array
    {
        $url = $n->url ?? '#';

        return match ($n->type) {
            'form_assigned' => [
                ['label' => 'Open form', 'url' => $url, 'style' => 'primary'],
                ['label' => 'Sign now',  'url' => $url . '#sign', 'style' => 'success'],
            ],
            'form_rejected' => [
                ['label' => 'View feedback', 'url' => $url, 'style' => 'primary'],
            ],
            'form_completed' => [
                ['label' => 'View form', 'url' => $url, 'style' => 'primary'],
            ],
            'reply' => [
                ['label' => 'View reply', 'url' => $url, 'style' => 'primary'],
            ],
            default => $url !== '#'
                ? [['label' => 'Open', 'url' => $url, 'style' => 'primary']]
                : [],
        };
    }

    /**
     * Bucket a timestamp into "today" / "yesterday" / "earlier" for grouping
     * in the tray. Matches the convention used by Slack, Linear, GitHub.
     */
    protected function dateBucket(\Carbon\Carbon|\Illuminate\Support\Carbon $when): string
    {
        if ($when->isToday())     return 'today';
        if ($when->isYesterday()) return 'yesterday';
        return 'earlier';
    }

    /**
     * Build a small actor payload (name + initials + avatar URL) for the
     * tray UI. Returns null when no actor is set (system notifications).
     */
    protected function actorPayload(?User $actor): ?array
    {
        if (!$actor) return null;

        $first = trim((string) $actor->first_name);
        $last  = trim((string) $actor->last_name);
        $name  = trim($first . ' ' . $last) ?: 'Someone';
        $initials = strtoupper(
            ($first ? mb_substr($first, 0, 1) : '') .
            ($last  ? mb_substr($last,  0, 1) : '')
        ) ?: strtoupper(mb_substr($name, 0, 1));

        // Profile pictures live at public/profile_pictures/<filename> in this app
        // (see User::getProfilePictureUrlAttribute) — NOT under storage/. Building
        // the URL via asset() matches the canonical accessor and lets the file be
        // served directly by the web server (no Laravel route hop).
        $avatar = null;
        if (!empty($actor->profile_picture)) {
            $pic = ltrim((string) $actor->profile_picture, '/');
            $avatar = $pic ? asset('profile_pictures/' . $pic) : null;
        }

        return [
            'name'     => $name,
            'initials' => $initials,
            'avatar'   => $avatar,
        ];
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

        // Empty the memo half of the tray too. recentMemos() keeps memos that were
        // read within the last 7 days visible (so "Mark all as read" doesn't appear
        // to wipe the tray) — but "Clear" is the explicit, confirmed wipe, so we
        // backdate read_at PAST that 7-day window to make the cards disappear now.
        // (comm_recipients.read_at only drives the tray window; the portal tracks
        // its own "last read" via MemoUserRead, so this doesn't corrupt that.)
        EmailCampaignRecipient::where('user_id', Auth::id())
            ->where(function ($q) {
                $q->where('is_read', false)
                  ->orWhere('read_at', '>=', now()->subDays(7));
            })
            ->update([
                'is_read' => true,
                'read_at' => now()->subDays(8),
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
        // SECURITY: department_id, staff_category and position_id are deliberately
        // NOT accepted here. They drive Forms leadership/VC routing, so letting a
        // user edit their own would be a privilege-escalation vector (e.g. a user
        // assigning themselves a leadership position to capture VC referrals).
        // Only a System Administrator can change them — see updateUserDetails().
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ], [
            'profile_picture.image' => 'The profile picture must be an image file.',
            'profile_picture.mimes' => 'The profile picture must be a JPEG, PNG, JPG, or GIF file.',
            'profile_picture.max' => 'The profile picture must not be larger than 5MB.',
        ]);

        try {
            // Fetch authenticated user
            $user = Auth::user();

            // Update user information (organization fields are intentionally excluded)
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');

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
        $search = trim((string) $request->get('search', ''));
        $filter = $request->get('filter', 'all');

        $query = User::with('position');

        if ($search !== '') {
            $tokens = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function ($outer) use ($tokens) {
                foreach ($tokens as $token) {
                    $like = '%' . $token . '%';
                    $outer->where(function ($q) use ($like) {
                        $q->where('first_name', 'like', $like)
                          ->orWhere('last_name', 'like', $like)
                          ->orWhere('email', 'like', $like);
                    });
                }
            });
        }

        if ($filter === 'approved') {
            $query->where('is_approve', 1);
        } elseif ($filter === 'pending') {
            $query->where('is_approve', 0);
        }

        $query->orderBy('created_at', 'desc');

        $users = $query->paginate($perPage)->withQueryString();
        $totalUsers = User::count();
        $approvedCount = User::where('is_approve', 1)->count();
        $pendingCount = User::where('is_approve', 0)->count();
        return view('admin.users',[
            'users' => $users,
            'totalUsers' => $totalUsers,
            'approvedCount' => $approvedCount,
            'pendingCount' => $pendingCount,
            'search' => $search,
            'activeFilter' => $filter,
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

    /**
     * Admin-only: update a user's info (email + the sensitive organization
     * fields: department, staff category, position). The organization fields
     * drive Forms leadership/VC routing and are intentionally NOT editable by
     * users themselves — only an administrator may change them here. Gated by
     * the institutional_admin middleware on the route.
     */
    public function updateUserDetails(Request $request, User $user)
    {
        if ($request->input('position_id') === '' || $request->input('position_id') === null) {
            $request->merge(['position_id' => null]);
        }

        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'department_id' => 'required|exists:departments,id',
            'staff_category' => 'required|string|in:Junior Staff,Senior Staff,Senior Member (Non-Teaching),Senior Member (Teaching)',
            'position_id' => 'nullable|exists:positions,id',
        ], [
            'email.unique' => 'This email is already in use by another account.',
            'department_id.required' => 'Please choose a Department/Faculty/Unit.',
            'staff_category.required' => 'Please choose a Staff Category.',
        ]);

        $user->email = $request->input('email');
        $user->department_id = $request->input('department_id');
        $user->staff_category = $request->input('staff_category');
        $user->position_id = $request->input('position_id');
        $user->save();

        return redirect()->route('dashboard.users')->with('success', "Details for {$user->first_name} {$user->last_name} updated successfully.");
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
                    // True when THIS user is the assignee of a form-linked memo
                    // that hasn't been unlocked yet — drives the list "Approval
                    // needed" tag. Cheap: no extra query (uses loaded columns).
                    // Excludes the Through intermediary: they can forward but never
                    // approve, so the tag must not appear for them.
                    'awaiting_my_approval' => $memo->hasLinkedForms()
                        && ! $memo->isFormUnlocked()
                        && (int) $memo->current_assignee_id === (int) $userId
                        && (int) $memo->through_user_id !== (int) $userId
                        && ($memo->memo_status ?? 'pending') === 'pending',
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
            
            // If creator, check if memo is assigned to someone else. Exception: the
            // originator of a form-request memo regains chat access once the recipient
            // approves & unlocks the form, so they can supply documents / answer
            // follow-ups without being formally re-assigned. While it is still under
            // review (not yet unlocked) the originator stays locked out as before.
            $creatorRegainedAccess = $isCreator && $memo->hasLinkedForms() && $memo->isFormUnlocked();
            $isAssignedToSomeoneElse = $isCreator
                && $memo->current_assignee_id
                && $memo->current_assignee_id != $userId
                && ! $creatorRegainedAccess;

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
                'ccRecipients.user',
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

            // Mark this user's memo-recipient row as read so the notification tray
            // badge/unread count clears — opening the memo here is equivalent to the
            // legacy readMemo page, which also set is_read.
            EmailCampaignRecipient::where('comm_campaign_id', $memo->id)
                ->where('user_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            // Get all users for assignment dropdown
            $users = User::with('department')
                ->where('is_approve', true)
                ->where('id', '!=', $userId)
                ->select('id', 'first_name', 'last_name', 'email', 'department_id')
                ->get();

            // Forms this memo's category maps to — drives the "Approve & Unlock"
            // button (approvers) and the "Proceed to fill form" banner (requester).
            $linkedForms = [];
            foreach ($memo->linkedFormSlugs() as $slug) {
                $def = app(\App\Forms\FormRegistry::class)->find($slug);
                if ($def) {
                    $linkedForms[] = (object) [
                        'slug'  => $def->slug(),
                        'title' => $def->title(),
                        'code'  => $def->code(),
                    ];
                }
            }

            return view('admin.uimms.chat', compact('memo', 'users', 'canParticipate', 'isAssignedToSomeoneElse', 'linkedForms'));

        } catch (\Exception $e) {
            \Log::error('Error in memoChat: ' . $e->getMessage());
            abort(500, 'Error loading memo chat.');
        }
    }

    public function exportMemoPdf(EmailCampaign $memo)
    {
        $userId = Auth::id();

        // Access check — same rules as memoChat
        $isActiveParticipant = $memo->isActiveParticipant($userId);
        $isRecipient         = $memo->recipients()->where('user_id', $userId)->exists();
        $isCreator           = $memo->created_by === $userId;

        if (!$isActiveParticipant && !$isRecipient && !$isCreator) {
            abort(403, 'You are not a participant in this memo.');
        }

        // PDF building lives in MemoExportService so the memo chat export, the
        // form-scoped "View approval" link, and the frozen form snapshot all
        // share one identical builder. This method only owns authorization.
        return app(\App\Services\Memo\MemoExportService::class)->stream($memo);
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
        
        // If creator, check if memo is assigned to someone else. Exception: the
        // originator of a form-request memo regains chat access once the recipient
        // approves & unlocks the form, so they can supply documents / answer
        // follow-ups without being formally re-assigned. While it is still under
        // review (not yet unlocked) the originator stays locked out as before.
        $creatorRegainedAccess = $isCreator && $memo->hasLinkedForms() && $memo->isFormUnlocked();
        $isAssignedToSomeoneElse = $isCreator
            && $memo->current_assignee_id
            && $memo->current_assignee_id != $userId
            && ! $creatorRegainedAccess;
        
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
            'message'            => 'nullable|string|max:5000',
            'attachments'        => 'nullable|array|max:5',
            'attachments.*'      => 'file|max:10240|mimes:pdf,doc,docx,txt,xls,xlsx,csv,ppt,pptx,jpg,jpeg,png,gif',
            'reply_mode'         => 'required|in:all,specific',
            'specific_recipients'=> 'nullable|string',
        ]);

        // Must have at least a message or an attachment — same as WhatsApp
        if (!$request->filled('message') && !$request->hasFile('attachments')) {
            return response()->json(['success' => false, 'message' => 'Please type a message or attach a file.'], 422);
        }

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
            'campaign_id'         => $memo->id,
            'user_id'             => $userId,
            'message'             => $request->message ?? '',
            'attachments'         => $attachments,
            'reply_mode'          => $request->reply_mode,
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
            'message' => 'nullable|string|max:1000',
        ]);

        $assigneeIds = $request->assignee_ids;
        $assignees = User::with('department')->whereIn('id', $assigneeIds)->get();

        if ($assignees->count() !== count($assigneeIds)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more selected users were not found.',
            ], 422);
        }

        // Auto-derive office from the single assignee's department; null for multiple
        $office = $assignees->count() === 1 ? $assignees->first()->department?->name : null;

        // Assign the memo to multiple users
        $memo->assignToMultiple($assigneeIds, $userId, $office);

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

        // No separate in-app assignment notification: assignToMultiple() above
        // (re)surfaces the memo as an unread card in each assignee's tray, and
        // recentMemos() labels it "Approval needed" (form-linked, not yet
        // unlocked) or "New memo" otherwise — so the assignee still sees exactly
        // what they must do, on ONE card instead of a duplicate alert. Email
        // notifications below are unchanged.

        // Send email notifications
        try {
            // Send success email to assigner with primary assignee (for email template compatibility)
            Mail::to(Auth::user()->email)->send(new \App\Mail\MemoAssignmentSuccess(
                $memo,
                Auth::user(),
                $assignees->first(),
                $office
            ));

            // Send notification email to each assignee
            foreach ($assignees as $assignee) {
                Mail::to($assignee->email)->send(new \App\Mail\MemoAssignedNotification(
                    $memo,
                    Auth::user(),
                    $assignee,
                    $office
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
     * "Through" forward: the intermediary releases the memo to its real recipients.
     *
     * Recipients are LOCKED to what the sender originally addressed (selected_users / cc_users);
     * the intermediary may only add a remark. This materialises the held recipient rows,
     * emails them, makes the To recipients active participants and flips through_status
     * to 'forwarded'. Until this runs, the recipients never saw the memo at all.
     */
    public function forwardThroughMemo(Request $request, EmailCampaign $memo)
    {
        $userId = Auth::id();

        // Only the named intermediary, and only while it is still awaiting forwarding.
        if ($memo->through_status !== 'pending' || (int) $memo->through_user_id !== (int) $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Only the Through person can forward this memo, and only while it is awaiting forwarding.',
            ], 403);
        }

        $request->validate([
            'message' => 'nullable|string|max:5000',
        ]);

        // Recipients are locked to the sender's original addressing.
        $toIds = collect($memo->selected_users ?? [])->map('intval')
            ->reject(fn ($id) => $id === (int) $userId)->unique()->values()->all();
        $ccIds = collect($memo->cc_users ?? [])->map('intval')
            ->reject(fn ($id) => $id === (int) $userId || in_array($id, $toIds, true))->unique()->values()->all();

        $toUsers = $toIds ? User::where('is_approve', true)->whereIn('id', $toIds)->get() : collect();
        $ccUsers = $ccIds ? User::where('is_approve', true)->whereIn('id', $ccIds)->get() : collect();

        if ($toUsers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'There are no valid recipients to forward this memo to.',
            ], 422);
        }

        $resendService = new ResendMailService();

        // Materialise the primary recipients + make them active participants.
        // assignToMultiple keeps the intermediary (assigner) active and sets the
        // recipients as the current holders; new rows default to recipient_role 'to'.
        $memo->assignToMultiple($toUsers->pluck('id')->toArray(), $userId, null);

        foreach ($toUsers as $user) {
            $this->sendMemoEmailTo($memo, $user, $resendService, $memo->subject, 'to');
        }

        // Materialise + email the held Cc recipients (deferred until forward).
        foreach ($ccUsers as $ccUser) {
            EmailCampaignRecipient::firstOrCreate([
                'comm_campaign_id' => $memo->id,
                'user_id' => $ccUser->id,
                'recipient_role' => 'cc',
            ], [
                'status' => 'pending',
                'is_active_participant' => false,
            ]);
            $this->sendMemoEmailTo($memo, $ccUser, $resendService, '[Cc] ' . $memo->subject, 'cc');
        }

        // Flip state + record the hand-off.
        $memo->update(['through_status' => 'forwarded']);
        $memo->addToWorkflowHistory('through_forwarded', $userId, $toUsers->pluck('id')->implode(','));

        // Post a chat note (visible to the now-active recipients). The tag names
        // both the recipient(s) and who it was routed THROUGH (this forwarder is
        // the named intermediary), so recipients see "Through {forwarder}".
        $names = $toUsers->map(fn ($u) => $u->first_name . ' ' . $u->last_name)->implode(', ');
        $forwarderName = Auth::user()->first_name . ' ' . Auth::user()->last_name;
        $forwardIcon = 'https://img.icons8.com/external-soft-fill-juicy-fish/50/external-forward-envelopes-and-mail-soft-fill-soft-fill-juicy-fish.png';
        $note = '<span style="display:inline-flex;align-items:center;flex-wrap:wrap;gap:8px;padding:6px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:20px;font-size:13px;font-weight:600;color:#1e3a8a;line-height:1.4;">'
            . '<img src="' . $forwardIcon . '" alt="Forwarded" style="width:18px;height:18px;flex:0 0 auto;">'
            . 'Memo forwarded to <strong style="font-weight:700;">' . e($names) . '</strong>'
            . '<span style="display:inline-flex;align-items:center;gap:6px;">'
            .     '<span style="background:#2563eb;color:#fff;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;padding:2px 9px;border-radius:10px;">Through</span>'
            .     '<strong style="font-weight:700;">' . e($forwarderName) . '</strong>'
            . '</span>'
            . '</span>';
        if ($request->filled('message')) {
            $note .= "<div style='margin:10px 0 6px;border-top:1px solid rgba(0,0,0,0.08);width:100%;'></div>" . nl2br(e($request->message));
        }
        MemoReply::create([
            'campaign_id' => $memo->id,
            'user_id' => $userId,
            'message' => $note,
            'attachments' => [],
        ]);

        // No separate "Memo forwarded to you" notification: assignToMultiple()
        // above already (re)surfaces the memo as an unread card in each recipient's
        // tray, and recentMemos() labels it "Forwarded to you". One card, not two.

        return response()->json([
            'success' => true,
            'message' => 'Memo forwarded to ' . $toUsers->count() . ' recipient(s).',
        ]);
    }

    /**
     * Send a single memo email via Resend and update that recipient row's status.
     * Shared by the Through-forward flow.
     */
    private function sendMemoEmailTo(EmailCampaign $campaign, User $user, ResendMailService $resendService, string $subject, string $role = 'to'): void
    {
        try {
            $htmlContent = view('mails.campaign_simple', [
                'campaign' => $campaign,
                'user' => $user,
                'subject' => $subject,
                'message' => $campaign->message,
            ])->render();

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

            $fromName = $campaign->creator ? trim($campaign->creator->first_name . ' ' . $campaign->creator->last_name) : (config('mail.from.name') ?: 'CUG');
            $result = $resendService->sendEmail(
                $user->email,
                $subject,
                $htmlContent,
                config('mail.from.address'),
                $attachments,
                0,
                $fromName
            );

            EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                ->where('user_id', $user->id)
                ->where('recipient_role', $role)
                ->update([
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'sent_at' => $result['success'] ? now() : null,
                    'error_message' => $result['success'] ? null : ('ResendMailService error: ' . ($result['error'] ?? 'Unknown error')),
                ]);

            usleep(300000);
        } catch (\Exception $e) {
            EmailCampaignRecipient::where('comm_campaign_id', $campaign->id)
                ->where('user_id', $user->id)
                ->where('recipient_role', $role)
                ->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }
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
     * Approve & Unlock the form linked to a categorised memo.
     *
     * Decoupled from memo_status on purpose (see EmailCampaign::unlockForms):
     * approving here invites the original requester to proceed to the matching
     * form; it never changes the conversation's completed/archived state. The
     * gate mirrors updateMemoStatus() — only the current assignee or an active
     * participant (the person/office the memo was routed to) may approve.
     */
    public function approveFormAccess(EmailCampaign $memo)
    {
        $userId = Auth::id();

        if (!$memo->hasLinkedForms()) {
            return response()->json([
                'success' => false,
                'message' => 'This memo is not linked to any form.',
            ], 422);
        }

        if ($memo->memo_status === 'archived') {
            return response()->json([
                'success' => false,
                'message' => 'This memo is archived and can no longer be approved.',
            ], 403);
        }

        if ($memo->isFormUnlocked()) {
            return response()->json([
                'success' => false,
                'message' => 'The form has already been unlocked for the requester.',
            ], 409);
        }

        // The Through intermediary forwards/minutes the memo but must never be the
        // one to approve the requester's form — only the actual recipient can. Guard
        // explicitly so the message is specific (before forwarding they are the current
        // assignee; after forwarding they remain an active participant).
        if ($memo->through_user_id && (int) $memo->through_user_id === (int) $userId) {
            return response()->json([
                'success' => false,
                'message' => 'As the Through person you can forward this request, but only the recipient it was addressed to can approve and unlock the form.',
            ], 403);
        }

        if (!$memo->canApproveForm($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Only the current assignee or active participants can approve this memo.',
            ], 403);
        }

        // Friendly label for the system message / notification.
        $registry = app(\App\Forms\FormRegistry::class);
        $formTitles = collect($memo->linkedFormSlugs())
            ->map(fn ($slug) => optional($registry->find($slug))->title())
            ->filter()
            ->values();
        $formLabel = $formTitles->count() === 1 ? $formTitles->first() : ($formTitles->count() . ' eligible forms');

        // Persist the unlock (writes form_unlocked_at/by + workflow history).
        $memo->unlockForms($userId);

        // Loop-closing side effects are best-effort: a failure here must never
        // undo the approval the approver just performed.
        // NOTE: the approval is intentionally NOT posted as a chat reply — the
        // requester is informed via the notification below, not in the memo chat.
        try {
            $actor = Auth::user();
            $actorName = trim(($actor->first_name ?? '') . ' ' . ($actor->last_name ?? ''));
            \App\Models\Notification::create([
                'user_id'  => $memo->created_by,
                'actor_id' => $userId,
                'type'     => 'memo',
                'category' => \App\Models\Notification::CATEGORY_MEMO,
                'title'    => 'Request Approved — Proceed to Form',
                'message'  => $actorName . ' approved your memo "' . $memo->subject . '". You can now proceed to fill the ' . $formLabel . '.',
                'url'      => route('dashboard.uimms.chat', $memo->id),
                'data'     => [
                    'memo_id'       => $memo->id,
                    'memo_category' => $memo->memo_category,
                    'form_slugs'    => $memo->linkedFormSlugs(),
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::warning('approveFormAccess: notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Form unlocked for the requester.',
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

    /**
     * Delete a chat message — only the sender can delete their own message
     */
    public function deleteChatMessage(MemoReply $reply)
    {
        $userId = Auth::id();

        if ($reply->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own messages.',
            ], 403);
        }

        $memo = $reply->campaign;
        if ($memo && in_array($memo->memo_status, ['completed', 'archived'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete messages in a ' . $memo->memo_status . ' memo.',
            ], 403);
        }

        // Delete attachment files from storage
        if ($reply->attachments) {
            foreach ($reply->attachments as $attachment) {
                $path = $attachment['path'] ?? null;
                if ($path && \Storage::disk('public')->exists($path)) {
                    \Storage::disk('public')->delete($path);
                }
            }
        }

        $replyId = $reply->id;
        $reply->delete();

        return response()->json([
            'success'  => true,
            'message'  => 'Message deleted',
            'reply_id' => $replyId,
        ]);
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
