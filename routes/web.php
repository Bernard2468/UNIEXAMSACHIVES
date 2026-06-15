<?php

use App\Http\Controllers\Dashboard\AcademicController;
use App\Http\Controllers\Dashboard\AdvanceCommunicationController;
 
use App\Http\Controllers\Dashboard\DepartmentController;
use App\Http\Controllers\Dashboard\DetailsController;
use App\Http\Controllers\Dashboard\FormPortalController;
use App\Http\Controllers\Dashboard\FormSubmissionController;
use App\Http\Controllers\Dashboard\PositionController;
use App\Http\Controllers\Dashboard\ExamsController;
use App\Http\Controllers\Dashboard\FilesController;
use App\Http\Controllers\Dashboard\FoldersController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\UserSignatureController;
use App\Http\Controllers\Frontend\EmailVerificationController;
use App\Http\Controllers\Frontend\PagesController;
use Illuminate\Support\Facades\Route;

// Landing Homepage - Clean professional landing page
Route::get('/', [PagesController::class, 'welcome'])->name('frontend.welcome');

// Admin Route for Advance Communication System Users
Route::get('/admin', [PagesController::class, 'adminLogin'])->name('admin.login');
Route::post('/admin', [PagesController::class, 'adminLoginUser'])->name('admin.login.user');
Route::post('/admin/access-request', [PagesController::class, 'adminAccessRequest'])->name('admin.access.request');

// Authentication Routes - Clean URLs
Route::get('/login', [PagesController::class, 'login'])->name('frontend.login');
Route::post('/login', [PagesController::class, 'loginUser'])->name('login');
Route::post('/register', [PagesController::class, 'register'])->name('register');

// Email Verification Routes (OTP)
Route::get('/verify-email', [EmailVerificationController::class, 'show'])->name('verification.show');
Route::post('/verify-email', [EmailVerificationController::class, 'verify'])
    ->middleware('throttle:10,1')
    ->name('verification.verify');
Route::post('/verify-email/resend', [EmailVerificationController::class, 'resend'])
    ->middleware('throttle:5,1')
    ->name('verification.resend');

// Password Reset Routes
Route::get('/forgot-password', [PagesController::class, 'forgotPassword'])->name('password.request');
Route::post('/forgot-password', [PagesController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PagesController::class, 'resetPassword'])->name('password.reset');
Route::post('/reset-password', [PagesController::class, 'updatePassword'])->name('password.update');

// Subscription Routes (accessible when no subscription exists)
Route::get('/subscription/locked', [\App\Http\Controllers\SubscriptionController::class, 'locked'])->name('subscription.locked');
Route::post('/subscription/subscribe', [\App\Http\Controllers\SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');

// Legacy redirect for old login-form URL
Route::get('/login-form', function() {
    return redirect('/login', 301);
});

// Additional Pages (uncommented for future use)
Route::get('/about', [PagesController::class, 'about'])->name('frontend.about');
Route::get('/news', [PagesController::class, 'news'])->name('frontend.news');
Route::get('/leadership', [PagesController::class, 'leadership'])->name('frontend.leadership');
Route::get('/faqs', [PagesController::class, 'faqs'])->name('frontend.faqs');
Route::post('/clear-session-messages', [App\Http\Controllers\NotificationController::class, 'clearSessionMessages'])->name('clear.session.messages');

// Maintenance Status Check API (accessible during maintenance)
Route::get('/api/check-maintenance-status', function() {
    $maintenanceMode = \App\Models\SystemSetting::getMaintenanceMode();
    
    // Also check for active scheduled maintenance that requires downtime
    $activeScheduledMaintenance = \App\Models\SystemMaintenanceLog::whereIn('status', ['in_progress', 'notified'])
        ->where('requires_downtime', true)
        ->where('scheduled_start', '<=', now())
        ->where('scheduled_end', '>', now())
        ->first();
    
    $activeMaintenance = $activeScheduledMaintenance ?? \App\Models\SystemMaintenanceLog::active()->first();
    
    // Maintenance is active if maintenance mode is enabled OR if there's active scheduled maintenance requiring downtime
    $maintenanceActive = $maintenanceMode || $activeScheduledMaintenance !== null;
    
    return response()->json([
        'maintenance_active' => $maintenanceActive,
        'maintenance_mode' => $maintenanceMode,
        'has_active_maintenance' => $activeMaintenance !== null,
        'scheduled_end' => $activeMaintenance?->scheduled_end?->toIso8601String(),
    ]);
})->name('api.check-maintenance-status');

# Authentication Required Routes
Route::middleware(['auth'])->group(function () {
    #Dashboard - Home
    Route::get('/dashboard/home',[HomeController::class, 'dashboard'])->name('dashboard');

    #Documents
    Route::get('/dashboard/create',[HomeController::class, 'create'])->name('dashboard.create');
    Route::get('/dashboard/file/create',[HomeController::class, 'createFile'])->name('dashboard.file.create');

    Route::get('/dashboard/exams-documents',[HomeController::class, 'document'])->name('dashboard.document');
    #All Exams Archive
    Route::get('/dashboard/upload-exams',[HomeController::class, 'uploadedDocument'])->name('dashboard.upload.document');
    Route::get('/dashboard/all-upload-exams',[HomeController::class, 'allUploadedDocument'])->name('dashboard.all.upload.document');

    #Unified Exams (No more pending/approved separation)
    Route::get('/dashboard/my-exams',[HomeController::class, 'myExams'])->name('dashboard.my.exams');
    Route::get('/dashboard/all-exams',[HomeController::class, 'allExams'])->name('dashboard.all.exams');

    #exams
    Route::post('/dashbaord/exam/store',[ExamsController::class, 'store'])->name('dashboard.exam.store');
    Route::get('/dashboard/exams/{exam}/edit', [ExamsController::class, 'edit'])->name('exams.edit');
    Route::put('/dashboard/exams/{exam}', [ExamsController::class, 'update'])->name('exams.update');

    Route::delete('/dashboard/exams/{exam}', [ExamsController::class, 'destroy'])->name('exams.destroy');
    Route::delete('/dashboard/exam/{exam}', [ExamsController::class, 'delete'])->name('exam.destroy');
    Route::get('/exams/filter', [ExamsController::class, 'filter']);
    Route::get('/exams/search', [ExamsController::class, 'search'])->name('exam.search');

    #Unified Files (No more pending/approved separation)
    Route::get('/dashboard/my-files',[FilesController::class, 'myFiles'])->name('dashboard.my.files');
    Route::get('/dashboard/all-files',[FilesController::class, 'allFiles'])->name('dashboard.all.files');

    #Files
    Route::post('/dashbaord/file/store',[FilesController::class, 'store'])->name('dashboard.file.store');
    Route::get('/dashboard/upload-files',[FilesController::class, 'uploadedFile'])->name('dashboard.upload.file');
    Route::get('/dashboard/all-upload-files',[FilesController::class, 'allUploadedFile'])->name('dashboard.all.upload.file');

    Route::get('/dashboard/file/{file}/edit', [FilesController::class, 'edit'])->name('files.edit');
    Route::put('/dashboard/files/{file}', [FilesController::class, 'update'])->name('files.update');

    Route::delete('/dashboard/file/{file}', [FilesController::class, 'destroy'])->name('file.destroy');

    #Folders
    Route::get('/dashboard/folders', [FoldersController::class, 'index'])->name('dashboard.folders.index');
    Route::post('/dashboard/folders', [FoldersController::class, 'store'])->name('dashboard.folders.store');
    Route::get('/dashboard/folders/{folder}', [FoldersController::class, 'show'])->name('dashboard.folders.show');
    Route::get('/dashboard/folders/{folder}/unlock', [FoldersController::class, 'unlockForm'])->name('dashboard.folders.unlock.form');
    Route::post('/dashboard/folders/{folder}/unlock', [FoldersController::class, 'unlock'])->name('dashboard.folders.unlock');
    Route::get('/dashboard/folders/{folder}/security', [FoldersController::class, 'security'])->name('dashboard.folders.security');
    Route::post('/dashboard/folders/{folder}/security', [FoldersController::class, 'updateSecurity'])->name('dashboard.folders.security.update');
    Route::get('/dashboard/folders/{folder}/edit', [FoldersController::class, 'edit'])->name('dashboard.folders.edit');
    Route::put('/dashboard/folders/{folder}', [FoldersController::class, 'update'])->name('dashboard.folders.update');
    Route::delete('/dashboard/folders/{folder}', [FoldersController::class, 'destroy'])->name('dashboard.folders.destroy');
    Route::post('/dashboard/folders/{folder}/add-files', [FoldersController::class, 'addFiles'])->name('dashboard.folders.add-files');
    Route::delete('/dashboard/folders/{folder}/files/{file}', [FoldersController::class, 'removeFile'])->name('dashboard.folders.remove-file');
    Route::post('/dashboard/folders/{folder}/add-exams', [FoldersController::class, 'addExams'])->name('dashboard.folders.add-exams');
    Route::delete('/dashboard/folders/{folder}/exams/{exam}', [FoldersController::class, 'removeExam'])->name('dashboard.folders.remove-exam');
    Route::post('/dashboard/folders/{folder}/move-item', [FoldersController::class, 'moveItem'])->name('dashboard.folders.move-item');

    // Folder sharing (members)
    Route::get('/dashboard/folders/users/search', [FoldersController::class, 'searchUsers'])->name('dashboard.folders.users.search');
    Route::get('/dashboard/folders/{folder}/members', [FoldersController::class, 'members'])->name('dashboard.folders.members');
    Route::post('/dashboard/folders/{folder}/share', [FoldersController::class, 'share'])->name('dashboard.folders.share');
    Route::patch('/dashboard/folders/{folder}/members/{user}', [FoldersController::class, 'updateMember'])->name('dashboard.folders.members.update');
    Route::delete('/dashboard/folders/{folder}/members/{user}', [FoldersController::class, 'unshare'])->name('dashboard.folders.unshare');
    Route::delete('/dashboard/folders/{folder}/leave', [FoldersController::class, 'leave'])->name('dashboard.folders.leave');

    #memos (replaces legacy broadcast message)
    Route::get('/dashboard/message',[HomeController::class, 'message'])->name('dashboard.message');
    Route::get('/dashboard/memos/unread-count', [HomeController::class, 'unreadMemoCount'])->name('dashboard.memos.unreadCount');
    Route::get('/dashboard/memos/recent', [HomeController::class, 'recentMemos'])->name('dashboard.memos.recent');
    Route::post('/dashboard/memos/mark-all-read', [HomeController::class, 'markAllMemosRead'])->name('dashboard.memos.markAllRead');
    Route::post('/dashboard/memos/{recipient}/mark-as-read', [HomeController::class, 'markSingleMemoAsRead'])->name('dashboard.memo.markAsRead');
    Route::get('/dashboard/memos/{recipient}', [HomeController::class, 'readMemo'])->name('dashboard.memo.read');
    Route::get('/dashboard/memos/{recipient}/attachment/{index}/download', [HomeController::class, 'downloadMemoAttachment'])->name('dashboard.memo.download-attachment');
    
    #memo replies
    Route::post('/dashboard/memos/{recipient}/reply', [HomeController::class, 'replyToMemo'])->name('dashboard.memo.reply');
    Route::get('/dashboard/memos/{recipient}/replies', [HomeController::class, 'viewMemoReplies'])->name('dashboard.memo.replies');
    Route::post('/dashboard/memos/replies/{reply}/mark-as-read', [HomeController::class, 'markReplyAsRead'])->name('dashboard.memo.reply.markAsRead');
    Route::get('/dashboard/memos/replies/{reply}/attachment/{index}/download', [HomeController::class, 'downloadReplyAttachment'])->name('dashboard.memo.reply.download-attachment');
    
    #UIMMS - Chat-based Memo Management System
    Route::get('/dashboard/uimms', [HomeController::class, 'uimmsPortal'])->name('dashboard.uimms.portal');
    Route::get('/dashboard/uimms/keep-in-view', [HomeController::class, 'keepInView'])->name('dashboard.uimms.keep-in-view');
    Route::get('/dashboard/uimms/memos/{status}', [HomeController::class, 'getMemosByStatus'])->name('dashboard.uimms.memos.byStatus');
    Route::get('/dashboard/uimms/bookmarked-memos', [HomeController::class, 'getBookmarkedMemos'])->name('dashboard.uimms.bookmarked-memos');
    Route::post('/dashboard/uimms/memo/{memo}/toggle-bookmark', [HomeController::class, 'toggleBookmark'])->name('dashboard.uimms.toggle-bookmark');
    Route::post('/dashboard/uimms/memo/{memo}/send-urgency-alert', [HomeController::class, 'sendUrgencyAlert'])->name('dashboard.uimms.send-urgency-alert');
    Route::get('/dashboard/uimms/chat/{memo}', [HomeController::class, 'memoChat'])->name('dashboard.uimms.chat');
    Route::post('/dashboard/uimms/chat/{memo}/message', [HomeController::class, 'sendChatMessage'])->name('dashboard.uimms.chat.message');
    Route::post('/dashboard/uimms/chat/{memo}/assign', [HomeController::class, 'assignMemo'])->name('dashboard.uimms.chat.assign');
    Route::post('/dashboard/uimms/chat/{memo}/forward-through', [HomeController::class, 'forwardThroughMemo'])->name('dashboard.uimms.chat.forward-through');
    Route::post('/dashboard/uimms/chat/{memo}/status', [HomeController::class, 'updateMemoStatus'])->name('dashboard.uimms.chat.status');
    Route::post('/dashboard/uimms/chat/{memo}/approve-form', [HomeController::class, 'approveFormAccess'])->name('dashboard.uimms.chat.approve-form');
    Route::post('/dashboard/uimms/bulk-archive-completed', [HomeController::class, 'bulkArchiveCompleted'])->name('dashboard.uimms.bulkArchiveCompleted');
    Route::post('/dashboard/uimms/bulk-archive-selected', [HomeController::class, 'bulkArchiveSelected'])->name('dashboard.uimms.bulkArchiveSelected');
    Route::post('/dashboard/uimms/bulk-reactivate', [HomeController::class, 'bulkReactivateSelected'])->name('dashboard.uimms.bulkReactivateSelected');
    Route::post('/dashboard/uimms/bulk-unarchive', [HomeController::class, 'bulkUnarchiveSelected'])->name('dashboard.uimms.bulkUnarchiveSelected');
    Route::get('/dashboard/uimms/chat/{memo}/attachment/{index}/download', [HomeController::class, 'downloadUimmsMemoAttachment'])->name('dashboard.uimms.chat.attachment.download');
    Route::get('/dashboard/uimms/chat/{memo}/attachment/{index}/view', [HomeController::class, 'viewUimmsMemoAttachment'])->name('dashboard.uimms.chat.attachment.view');
    Route::get('/dashboard/uimms/chat/{memo}/messages', [HomeController::class, 'getChatMessages'])->name('dashboard.uimms.chat.messages');
    Route::get('/dashboard/uimms/chat/{memo}/export-pdf', [HomeController::class, 'exportMemoPdf'])->name('dashboard.uimms.chat.export-pdf');
    Route::get('/dashboard/uimms/chat/reply/{reply}/attachment/{index}/download', [HomeController::class, 'downloadUimmsChatAttachment'])->name('dashboard.uimms.chat.reply.attachment.download');
    Route::get('/dashboard/uimms/chat/reply/{reply}/attachment/{index}/view', [HomeController::class, 'viewUimmsChatAttachment'])->name('dashboard.uimms.chat.reply.attachment.view');
    Route::delete('/dashboard/uimms/chat/reply/{reply}', [HomeController::class, 'deleteChatMessage'])->name('dashboard.uimms.chat.reply.delete');
    
    #notifications
    Route::get('/dashboard/notifications', [HomeController::class, 'getNotifications'])->name('dashboard.notifications');
    Route::get('/dashboard/notifications/check', [HomeController::class, 'checkNewNotifications'])->name('dashboard.notifications.check');
    Route::post('/dashboard/notifications/{id}/mark-read', [HomeController::class, 'markNotificationAsRead'])->name('dashboard.notification.markAsRead');
    Route::post('/dashboard/notifications/mark-all-read', [HomeController::class, 'markAllNotificationsAsRead'])->name('dashboard.notifications.markAllRead');

    # Calendar Events
    Route::get('/dashboard/calendar/events', [\App\Http\Controllers\Dashboard\CalendarController::class, 'index'])->name('dashboard.calendar.events');
    Route::post('/dashboard/calendar/events', [\App\Http\Controllers\Dashboard\CalendarController::class, 'store'])->name('dashboard.calendar.store');
    Route::delete('/dashboard/calendar/events/{id}', [\App\Http\Controllers\Dashboard\CalendarController::class, 'destroy'])->name('dashboard.calendar.destroy');
    Route::post('/dashboard/notifications/mark-all-unified', [HomeController::class, 'markAllUnifiedAsRead'])->name('dashboard.notifications.markAllUnified');
    Route::delete('/dashboard/notifications/clear-all', [HomeController::class, 'clearAllNotifications'])->name('dashboard.notifications.clearAll');

    # Browser Push (Web Push API)
    Route::get('/dashboard/push/config',         [\App\Http\Controllers\Dashboard\PushSubscriptionController::class, 'config'])->name('push.config');
    Route::post('/dashboard/push/subscribe',     [\App\Http\Controllers\Dashboard\PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/dashboard/push/unsubscribe',   [\App\Http\Controllers\Dashboard\PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::post('/dashboard/push/toggle',        [\App\Http\Controllers\Dashboard\PushSubscriptionController::class, 'togglePushEnabled'])->name('push.toggle');

    #profile
    Route::get('/dashboard/profile',[HomeController::class, 'profile'])->name('dashboard.profile');
    
    # File Downloads
    Route::get('/download/exam/{exam}', [App\Http\Controllers\Dashboard\ExamsController::class, 'downloadExam'])->name('download.exam');
    Route::get('/download/answer-key/{exam}', [App\Http\Controllers\Dashboard\ExamsController::class, 'downloadAnswerKey'])->name('download.answer.key');
    Route::get('/download/file/{file}', [App\Http\Controllers\Dashboard\FilesController::class, 'downloadFile'])->name('download.file');
    
    # /storage/* file server.
    #
    # On hosts that allow `php artisan storage:link` to create the symlink,
    # Apache serves these requests directly from public/storage and never
    # reaches this handler. On shared hosts where the symlink() PHP function
    # is disabled (Hostinger, many cPanel providers), the request falls
    # through to here and we serve the file via PHP instead. Same URL,
    # same auth posture — just two ways to satisfy it.
    Route::get('/storage/{path}', function (string $path) {
        // Guard against path traversal — refuse anything that escapes the
        // public storage directory after canonicalisation.
        $baseDir   = realpath(storage_path('app/public'));
        $fullPath  = realpath(storage_path('app/public/' . $path));
        if ($fullPath && $baseDir && str_starts_with($fullPath, $baseDir) && is_file($fullPath)) {
            return response()->file($fullPath, [
                'Cache-Control' => 'private, max-age=86400',
            ]);
        }

        // Legacy fallback: old exam URLs that lived under public/exams/documents/
        $legacy = public_path('exams/documents/' . basename($path));
        if (is_file($legacy)) {
            return redirect(asset('exams/documents/' . basename($path)));
        }

        abort(404, 'File not found.');
    })->where('path', '.*');

    #settings
    Route::get('/dashboard/settings',[HomeController::class, 'settings'])->name('dashboard.settings');
    Route::post('/dashboard/update-user-info',[HomeController::class,'updateUserInfo'])->name('dashboard.user.info');
    Route::post('/dashboard/update-password',[HomeController::class,'updatePassword'])->name('dashboard.password.update');

    #licenses
    Route::get('/dashboard/system-licences',[\App\Http\Controllers\Dashboard\LicenseController::class, 'index'])->name('dashboard.system-licences');

    #system documentation (regular users - view only)
    Route::get('/dashboard/system-documentation',[\App\Http\Controllers\Dashboard\SystemDocumentationController::class, 'index'])->name('dashboard.system-documentation');
    Route::get('/dashboard/system-documentation/{id}/preview',[\App\Http\Controllers\Dashboard\SystemDocumentationController::class, 'preview'])->name('dashboard.system-documentation.preview');
    Route::get('/dashboard/system-documentation/{id}/download',[\App\Http\Controllers\Dashboard\SystemDocumentationController::class, 'download'])->name('dashboard.system-documentation.download');

    #system documentation (admin users - manage) - Only accessible to non-employee users (admins)
    Route::get('/dashboard/system-documentation/manage',[\App\Http\Controllers\Dashboard\AdminSystemDocumentationController::class, 'index'])->name('dashboard.system-documentation.manage');
    Route::post('/dashboard/system-documentation/manage',[\App\Http\Controllers\Dashboard\AdminSystemDocumentationController::class, 'store'])->name('dashboard.system-documentation.manage.store');
    Route::put('/dashboard/system-documentation/manage/{id}',[\App\Http\Controllers\Dashboard\AdminSystemDocumentationController::class, 'update'])->name('dashboard.system-documentation.manage.update');
    Route::delete('/dashboard/system-documentation/manage/{id}',[\App\Http\Controllers\Dashboard\AdminSystemDocumentationController::class, 'destroy'])->name('dashboard.system-documentation.manage.destroy');
    Route::get('/dashboard/system-documentation/manage/{id}/preview',[\App\Http\Controllers\Dashboard\AdminSystemDocumentationController::class, 'preview'])->name('dashboard.system-documentation.manage.preview');
    Route::get('/dashboard/system-documentation/manage/{id}/download',[\App\Http\Controllers\Dashboard\AdminSystemDocumentationController::class, 'download'])->name('dashboard.system-documentation.manage.download');

    #user manual (regular users - view only)
    Route::get('/dashboard/user-manual',[\App\Http\Controllers\Dashboard\UserManualController::class, 'index'])->name('dashboard.user-manual');
    Route::get('/dashboard/user-manual/{id}/preview',[\App\Http\Controllers\Dashboard\UserManualController::class, 'preview'])->name('dashboard.user-manual.preview');
    Route::get('/dashboard/user-manual/{id}/download',[\App\Http\Controllers\Dashboard\UserManualController::class, 'download'])->name('dashboard.user-manual.download');

    #user manual (admin users - manage) - Only accessible to non-employee users (admins)
    Route::get('/dashboard/user-manual/manage',[\App\Http\Controllers\Dashboard\AdminUserManualController::class, 'index'])->name('dashboard.user-manual.manage');
    Route::post('/dashboard/user-manual/manage',[\App\Http\Controllers\Dashboard\AdminUserManualController::class, 'store'])->name('dashboard.user-manual.manage.store');
    Route::put('/dashboard/user-manual/manage/{id}',[\App\Http\Controllers\Dashboard\AdminUserManualController::class, 'update'])->name('dashboard.user-manual.manage.update');
    Route::delete('/dashboard/user-manual/manage/{id}',[\App\Http\Controllers\Dashboard\AdminUserManualController::class, 'destroy'])->name('dashboard.user-manual.manage.destroy');
    Route::get('/dashboard/user-manual/manage/{id}/preview',[\App\Http\Controllers\Dashboard\AdminUserManualController::class, 'preview'])->name('dashboard.user-manual.manage.preview');
    Route::get('/dashboard/user-manual/manage/{id}/download',[\App\Http\Controllers\Dashboard\AdminUserManualController::class, 'download'])->name('dashboard.user-manual.manage.download');

    #users — institutional-admin only. These are sensitive operations (create,
    # approve, delete, change email, change organization/position which drives
    # Forms leadership/VC routing). Gated server-side, not just hidden in the UI,
    # so a normal user cannot reach them by crafting a direct request.
    Route::middleware('institutional_admin')->group(function () {
        Route::get('/dashboard/users',[HomeController::class, 'users'])->name('dashboard.users');
        Route::post('/dashboard/users', [HomeController::class, 'storeUser'])->name('users.store');
        Route::post('/dashboard/users/{user}/approve', [HomeController::class, 'approve'])->name('users.approve');
        Route::post('/dashboard/users/{user}/disapprove', [HomeController::class, 'disapprove'])->name('users.disapprove');
        Route::delete('/dashboard/users/{user}', [HomeController::class, 'destroy'])->name('users.destroy');
        Route::patch('/dashboard/users/{user}/details', [HomeController::class, 'updateUserDetails'])->name('users.update-details');
    });

    #systems detail
    Route::post('/dashboard/system-details/store', [DetailsController::class, 'store'])->name('dashboard.details.store');

    #department
    Route::resource('departments', DepartmentController::class);

    #positions
    Route::resource('positions', PositionController::class);

    #offices (institutional offices that the Forms workflow routes through)
    Route::middleware('institutional_admin')->group(function () {
        Route::get('/offices',                            [\App\Http\Controllers\Dashboard\OfficeController::class, 'index'])->name('offices.index');
        Route::post('/offices',                           [\App\Http\Controllers\Dashboard\OfficeController::class, 'store'])->name('offices.store');
        Route::get('/offices/{office}',                   [\App\Http\Controllers\Dashboard\OfficeController::class, 'show'])->name('offices.show');
        Route::put('/offices/{office}',                   [\App\Http\Controllers\Dashboard\OfficeController::class, 'update'])->name('offices.update');
        Route::delete('/offices/{office}',                [\App\Http\Controllers\Dashboard\OfficeController::class, 'destroy'])->name('offices.destroy');
        Route::post('/offices/{office}/members',          [\App\Http\Controllers\Dashboard\OfficeController::class, 'addMember'])->name('offices.members.add');
        Route::put('/offices/{office}/members/{user}',    [\App\Http\Controllers\Dashboard\OfficeController::class, 'updateMember'])->name('offices.members.update');
        Route::delete('/offices/{office}/members/{user}', [\App\Http\Controllers\Dashboard\OfficeController::class, 'removeMember'])->name('offices.members.remove');
    });

    #system letterheads (admin-managed letterheads for memo composer)
    Route::get('/dashboard/system-letterheads', [\App\Http\Controllers\Dashboard\SystemLetterheadController::class, 'index'])->name('dashboard.system-letterheads.index');
    Route::post('/dashboard/system-letterheads', [\App\Http\Controllers\Dashboard\SystemLetterheadController::class, 'store'])->name('dashboard.system-letterheads.store');
    Route::put('/dashboard/system-letterheads/{system_letterhead}', [\App\Http\Controllers\Dashboard\SystemLetterheadController::class, 'update'])->name('dashboard.system-letterheads.update');
    Route::post('/dashboard/system-letterheads/{system_letterhead}/toggle', [\App\Http\Controllers\Dashboard\SystemLetterheadController::class, 'toggle'])->name('dashboard.system-letterheads.toggle');
    Route::post('/dashboard/system-letterheads/reorder', [\App\Http\Controllers\Dashboard\SystemLetterheadController::class, 'reorder'])->name('dashboard.system-letterheads.reorder');
    Route::delete('/dashboard/system-letterheads/{system_letterhead}', [\App\Http\Controllers\Dashboard\SystemLetterheadController::class, 'destroy'])->name('dashboard.system-letterheads.destroy');

    #committees & boards
    Route::resource('committees', \App\Http\Controllers\Dashboard\CommitteesController::class)->except(['show', 'create', 'edit']);
    Route::get('/committees/{committee}/show', [\App\Http\Controllers\Dashboard\CommitteesController::class, 'show'])->name('committees.show');
    Route::post('/committees/{committee}/add-users', [\App\Http\Controllers\Dashboard\CommitteesController::class, 'addUsers'])->name('committees.add-users');
    Route::delete('/committees/{committee}/users/{user}', [\App\Http\Controllers\Dashboard\CommitteesController::class, 'removeUser'])->name('committees.remove-user');
    Route::get('/committees/my-committees', [\App\Http\Controllers\Dashboard\CommitteesController::class, 'myCommittees'])->name('committees.my-committees');

    #Academic Year
    Route::post('/dashboard/academic-year/store', [AcademicController::class, 'store'])->name('dashboard.academic.store');

    #Payment History
    Route::get('/dashboard/payment-history', [\App\Http\Controllers\Dashboard\PaymentHistoryController::class, 'index'])->name('dashboard.payment-history.index');
    Route::get('/dashboard/payment-history/{id}/invoice', [\App\Http\Controllers\Dashboard\PaymentHistoryController::class, 'viewInvoice'])->name('dashboard.payment-history.view-invoice');
    Route::get('/dashboard/payment-history/{id}/invoice/download', [\App\Http\Controllers\Dashboard\PaymentHistoryController::class, 'downloadInvoice'])->name('dashboard.payment-history.download-invoice');

    #Advanced Communication System (Users Only)
    Route::prefix('admin/communication')->name('admin.communication.')->middleware('auth')->group(function () {
        Route::get('/', [AdvanceCommunicationController::class, 'index'])->name('index');
        Route::get('/create', [AdvanceCommunicationController::class, 'create'])->name('create');
        Route::post('/store', [AdvanceCommunicationController::class, 'store'])->name('store');
        Route::get('/{campaign}', [AdvanceCommunicationController::class, 'show'])->name('show');
        Route::get('/{campaign}/edit', [AdvanceCommunicationController::class, 'edit'])->name('edit');
        Route::put('/{campaign}', [AdvanceCommunicationController::class, 'update'])->name('update');
        Route::delete('/{campaign}', [AdvanceCommunicationController::class, 'destroy'])->name('destroy');
        Route::post('/{campaign}/send', [AdvanceCommunicationController::class, 'send'])->name('send');
        Route::get('/statistics/overview', [AdvanceCommunicationController::class, 'statistics'])->name('statistics');
        Route::get('/{campaign}/attachment/{index}/download', [AdvanceCommunicationController::class, 'downloadAttachment'])->name('download-attachment');
        Route::delete('/{campaign}/attachment/{index}', [AdvanceCommunicationController::class, 'removeAttachment'])->name('remove-attachment');
        Route::get('/ajax/users', [AdvanceCommunicationController::class, 'getUsersAjax'])->name('users.ajax');
        Route::get('/{campaign}/replies', [AdvanceCommunicationController::class, 'viewReplies'])->name('replies');
    });

    #Advanced Communication System (Admin Only)
    Route::prefix('admin/communication-admin')->name('admin.communication-admin.')->middleware('auth')->group(function () {
        Route::get('/', [AdvanceCommunicationController::class, 'adminIndex'])->name('index');
        Route::get('/create', [AdvanceCommunicationController::class, 'adminCreate'])->name('create');
        Route::post('/store', [AdvanceCommunicationController::class, 'adminStore'])->name('store');
        Route::get('/{campaign}', [AdvanceCommunicationController::class, 'adminShow'])->name('show');
        Route::get('/{campaign}/edit', [AdvanceCommunicationController::class, 'adminEdit'])->name('edit');
        Route::put('/{campaign}', [AdvanceCommunicationController::class, 'adminUpdate'])->name('update');
        Route::delete('/{campaign}', [AdvanceCommunicationController::class, 'adminDestroy'])->name('destroy');
        Route::post('/{campaign}/send', [AdvanceCommunicationController::class, 'adminSend'])->name('send');
        Route::get('/statistics/overview', [AdvanceCommunicationController::class, 'adminStatistics'])->name('statistics');
        Route::get('/{campaign}/attachment/{index}/download', [AdvanceCommunicationController::class, 'adminDownloadAttachment'])->name('download-attachment');
        Route::delete('/{campaign}/attachment/{index}', [AdvanceCommunicationController::class, 'adminRemoveAttachment'])->name('remove-attachment');
        Route::get('/ajax/users', [AdvanceCommunicationController::class, 'adminGetUsersAjax'])->name('users.ajax');
        Route::get('/{campaign}/replies', [AdvanceCommunicationController::class, 'adminViewReplies'])->name('replies');
    });

    #Forms Workflow System (Purchase/Works Authorization, Payment Requisition, future forms)
    Route::prefix('admin/forms')->name('admin.forms.')->group(function () {
        Route::get('/',                                  [FormPortalController::class, 'index'])->name('portal');
        Route::get('/all',                               [FormSubmissionController::class, 'gallery'])->name('gallery');
        Route::get('/compose/{formSlug}',                [FormSubmissionController::class, 'compose'])->name('compose');
        Route::post('/compose/{formSlug}',               [FormSubmissionController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('store');

        Route::get('/submissions/{submission}',          [FormSubmissionController::class, 'show'])->name('show');
        Route::post('/submissions/{submission}/draft',   [FormSubmissionController::class, 'saveDraft'])
            ->middleware('throttle:60,1')
            ->name('save-draft');
        Route::post('/submissions/{submission}/sign',    [FormSubmissionController::class, 'sign'])
            ->middleware('throttle:30,1')
            ->name('sign');
        Route::post('/submissions/{submission}/reject',  [FormSubmissionController::class, 'reject'])
            ->middleware('throttle:30,1')
            ->name('reject');
        Route::post('/submissions/{submission}/cancel',  [FormSubmissionController::class, 'cancel'])->name('cancel');
        Route::post('/submissions/{submission}/reassign',[FormSubmissionController::class, 'reassign'])
            ->middleware('throttle:30,1')
            ->name('reassign');
        Route::post('/submissions/{submission}/comments',[FormSubmissionController::class, 'addComment'])
            ->middleware('throttle:120,1')
            ->name('comment');
        Route::get('/submissions/{submission}/pdf',      [FormSubmissionController::class, 'downloadPdf'])->name('pdf');
        Route::get('/submissions/{submission}/attachments/{attachment}', [FormSubmissionController::class, 'downloadAttachment'])->name('attachment');

        Route::get('/offices/{officeSlug}/members',      [FormSubmissionController::class, 'officeMembers'])->name('office-members');

        Route::post('/my-signature',   [UserSignatureController::class, 'update'])->name('my-signature.update');
        Route::delete('/my-signature', [UserSignatureController::class, 'destroy'])->name('my-signature.destroy');
    });

    #logout
    Route::get('/logout',[HomeController::class, 'logout'])->name('logout');
});

// ===== ADMIN SUBSCRIPTION RENEWAL ROUTE =====
// Allow regular admins (role='user' in database, displayed as "Admin" in UI) to renew subscriptions
// Note: Database 'user' = UI "Admin" - see ROLE_TERMINOLOGY.md for details
Route::post('/subscriptions/{id}/renew', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'renew'])
    ->middleware(['auth'])
    ->name('admin.subscriptions.renew');

// ===== SUPER ADMIN ROUTES =====

// Super Admin Login (separate from main login)
Route::get('/super-admin/login', function() {
    if (auth()->check() && auth()->user()->isSuperAdmin()) {
        return redirect()->route('super-admin.dashboard');
    }
    return view('super-admin.login');
})->name('super-admin.login');

Route::post('/super-admin/login', [\App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'login'])->name('super-admin.login.post');

// Payment callback (no auth required - payment reference is used for verification)
// MUST be defined BEFORE the super-admin group to avoid middleware conflicts
// This is needed because Paystack redirects back and session might be lost
Route::get('/super-admin/payments/callback', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'callback'])
    ->name('super-admin.payments.callback');

// Protected Super Admin Routes
// Note: Only using 'super_admin' middleware which handles auth check and redirects to super-admin.login
Route::prefix('super-admin')->name('super-admin.')->middleware(['super_admin'])->group(function () {
    
    // Dashboard
    Route::get('/', [\App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'dashboard'])->name('dashboard');
    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'analytics'])->name('analytics');
    // User Role Management
    Route::get('/roles', [\App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'manageRoles'])->name('roles.index');
    Route::post('/users/{id}/grant-super-admin', [\App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'grantSuperAdmin'])->name('users.grant-super-admin');
    Route::post('/users/{id}/revoke-super-admin', [\App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'revokeSuperAdmin'])->name('users.revoke-super-admin');
    Route::post('/users/{id}/grant-admin', [\App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'grantAdmin'])->name('users.grant-admin');
    Route::post('/users/{id}/revoke-admin', [\App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'revokeAdmin'])->name('users.revoke-admin');
    
    // Subscription Plan Management
    Route::resource('subscription-plans', \App\Http\Controllers\SuperAdmin\SubscriptionPlanController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/subscription-plans/{id}/toggle-active', [\App\Http\Controllers\SuperAdmin\SubscriptionPlanController::class, 'toggleActive'])->name('subscription-plans.toggle-active');

    // Subscription Management
    Route::resource('subscriptions', \App\Http\Controllers\SuperAdmin\SubscriptionController::class);
    Route::post('/subscriptions/{id}/renew', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::post('/subscriptions/{id}/suspend', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
    Route::post('/subscriptions/{id}/reactivate', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');
    Route::get('/subscriptions-export', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'export'])->name('subscriptions.export');
    Route::post('/subscriptions/bulk-update-statuses', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'bulkUpdateStatuses'])->name('subscriptions.bulk-update-statuses');
    
    // Payment Management
    Route::get('/payments', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{id}', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{id}/retry', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'retry'])->name('payments.retry');
    Route::post('/payments/{id}/refund', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'refund'])->name('payments.refund');
    Route::get('/payments/{id}/receipt', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'viewReceipt'])->name('payments.receipt');
    Route::get('/payments/{id}/receipt/download', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'downloadReceipt'])->name('payments.receipt.download');
    Route::get('/payments-export', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'export'])->name('payments.export');
    Route::get('/payments-report', [\App\Http\Controllers\SuperAdmin\PaymentController::class, 'report'])->name('payments.report');
    
    // Maintenance Management
    Route::resource('maintenance', \App\Http\Controllers\SuperAdmin\MaintenanceController::class);
    Route::post('/maintenance/{id}/start', [\App\Http\Controllers\SuperAdmin\MaintenanceController::class, 'start'])->name('maintenance.start');
    Route::post('/maintenance/{id}/complete', [\App\Http\Controllers\SuperAdmin\MaintenanceController::class, 'complete'])->name('maintenance.complete');
    Route::post('/maintenance/{id}/cancel', [\App\Http\Controllers\SuperAdmin\MaintenanceController::class, 'cancel'])->name('maintenance.cancel');
    Route::post('/maintenance/{id}/rollback', [\App\Http\Controllers\SuperAdmin\MaintenanceController::class, 'rollback'])->name('maintenance.rollback');
    Route::post('/maintenance/{id}/approve', [\App\Http\Controllers\SuperAdmin\MaintenanceController::class, 'approve'])->name('maintenance.approve');
    Route::post('/maintenance/{id}/notify', [\App\Http\Controllers\SuperAdmin\MaintenanceController::class, 'notifyUsers'])->name('maintenance.notify');
    
    // License Management
    Route::get('/system-licences', [\App\Http\Controllers\SuperAdmin\LicenseController::class, 'index'])->name('system-licences');
    Route::post('/licenses', [\App\Http\Controllers\SuperAdmin\LicenseController::class, 'store'])->name('licenses.store');
    Route::put('/licenses/{id}', [\App\Http\Controllers\SuperAdmin\LicenseController::class, 'update'])->name('licenses.update');
    Route::post('/licenses/{id}/toggle-status', [\App\Http\Controllers\SuperAdmin\LicenseController::class, 'toggleStatus'])->name('licenses.toggle-status');
    Route::delete('/licenses/{id}', [\App\Http\Controllers\SuperAdmin\LicenseController::class, 'destroy'])->name('licenses.destroy');
    
    // System Settings
    Route::get('/settings', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/single', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'updateSingle'])->name('settings.update-single');
    Route::post('/settings/test-paystack', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'testPaystack'])->name('settings.test-paystack');
    Route::post('/settings/toggle-maintenance', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'toggleMaintenanceMode'])->name('settings.toggle-maintenance');
    Route::post('/settings/clear-cache', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('/settings/optimize', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'optimize'])->name('settings.optimize');
    Route::get('/settings/export', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'export'])->name('settings.export');
    Route::post('/settings/import', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'import'])->name('settings.import');
    Route::post('/settings/reset', [\App\Http\Controllers\SuperAdmin\SystemSettingsController::class, 'reset'])->name('settings.reset');
});

// Webhook endpoints (no auth required)
Route::post('/webhooks/paystack', [\App\Http\Controllers\SuperAdmin\WebhookController::class, 'paystack'])->name('webhooks.paystack');
Route::post('/webhooks/test', [\App\Http\Controllers\SuperAdmin\WebhookController::class, 'test'])->name('webhooks.test');
