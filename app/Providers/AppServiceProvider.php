<?php

namespace App\Providers;

use App\Forms\Contracts\SignatureProvider;
use App\Forms\Definitions\AnnualLeaveApplicationForm;
use App\Forms\Definitions\PaymentRequisitionForm;
use App\Forms\Definitions\PurchaseWorksAuthorizationForm;
use App\Forms\FormRegistry;
use App\Models\Detail;
use App\Models\Exam;
use App\Models\File;
use App\Models\FormSubmission;
use App\Models\Message;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\Notification;
use App\Observers\NotificationObserver;
use App\Policies\FormSubmissionPolicy;
use App\Services\Signing\InAppSignatureProvider;
use App\Services\Signing\SignatureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ===== Forms: signing backend =====
        // To switch to DocuSign/PandaDoc/SignNow later, bind a different
        // SignatureProvider implementation here. No other code changes needed.
        $this->app->singleton(SignatureProvider::class, InAppSignatureProvider::class);
        $this->app->singleton(SignatureService::class, function ($app) {
            return new SignatureService($app->make(SignatureProvider::class));
        });

        // ===== Forms: registry of available form types =====
        // Adding a new form = create a class extending BaseFormDefinition and
        // register it here. The renderer, controllers, sidebar and PDF
        // pipeline pick it up automatically.
        $this->app->singleton(FormRegistry::class, function () {
            $registry = new FormRegistry();
            $registry->register(new PaymentRequisitionForm());
            $registry->register(new PurchaseWorksAuthorizationForm());
            $registry->register(new AnnualLeaveApplicationForm());
            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure authentication redirects
        $this->configureAuthenticationRedirects();

        // Forms authorisation: explicitly bind policy so it is discoverable
        // regardless of Laravel's auto-discovery conventions.
        Gate::policy(FormSubmission::class, FormSubmissionPolicy::class);

        // Mirror every newly-created Notification to the user's browser push
        // subscriptions (no-op when VAPID keys aren't configured).
        Notification::observe(NotificationObserver::class);

        View::composer('*', function ($view) {
            if(Auth::check()){
                $userId = Auth::id();
                
                // Get bookmarked memo IDs for the current user
                // Query pivot table directly to avoid ambiguous column error
                $bookmarkedMemoIds = DB::table('memo_user_bookmarks')
                    ->where('user_id', $userId)
                    ->pluck('campaign_id')
                    ->toArray();
                
                // Calculate message counts for all authenticated users, excluding bookmarked memos
                $newMessagesQuery = EmailCampaignRecipient::where('user_id', $userId)
                    ->where('is_read', false);
                    
                if (!empty($bookmarkedMemoIds)) {
                    $newMessagesQuery->whereNotIn('comm_campaign_id', $bookmarkedMemoIds);
                }
                $newMessagesCount = $newMessagesQuery->count();
                
                $totalMemosQuery = EmailCampaignRecipient::where('user_id', $userId);
                if (!empty($bookmarkedMemoIds)) {
                    $totalMemosQuery->whereNotIn('comm_campaign_id', $bookmarkedMemoIds);
                }
                $totalMemosCount = $totalMemosQuery->count();
                
                // Calculate bookmarked memos count
                $bookmarkedCount = count($bookmarkedMemoIds);

                // Unread pending memos (portal Unread tab logic) for sidebar counter
                $unreadMemosCount = EmailCampaign::countUnreadPendingForUser($userId);

                // Forms awaiting this user's action (sidebar badge for FORMS section)
                $awaitingFormsCount = FormSubmission::where('status', FormSubmission::STATUS_IN_PROGRESS)
                    ->where('current_assignee_id', $userId)
                    ->count();

                if (Auth::user()->is_admin) {
                    // Regular users (is_admin = 1) - see only their own exams/files
                    $myExams = Exam::where('user_id', Auth::user()->id)->count();
                    $myFiles = File::where('user_id', Auth::user()->id)->count();

                    $view->with([
                        'newMessagesCount' => $newMessagesCount,
                        'totalMemosCount' => $totalMemosCount,
                        'unreadMemosCount' => $unreadMemosCount,
                        'bookmarkedCount' => $bookmarkedCount,
                        'awaitingFormsCount' => $awaitingFormsCount,
                        'myExamsCount' => $myExams,
                        'allExamsCount' => $myExams, // For regular users, "all" is just their own
                        'myFilesCount' => $myFiles,
                        'allFilesCount' => $myFiles, // For regular users, "all" is just their own
                        'systemDetail' => Detail::all(),
                        'showPasswordReminder' => !Auth::user()->password_changed,
                    ]);
                }else{
                    // Admin users (is_admin = 0) - also see only their own exams/files (no approval system)
                    $myExams = Exam::where('user_id', Auth::user()->id)->count();
                    $myFiles = File::where('user_id', Auth::user()->id)->count();
                    
                    $view->with([
                        'newMessagesCount' => $newMessagesCount,
                        'totalMemosCount' => $totalMemosCount,
                        'unreadMemosCount' => $unreadMemosCount,
                        'bookmarkedCount' => $bookmarkedCount,
                        'awaitingFormsCount' => $awaitingFormsCount,
                        'myExamsCount' => $myExams,
                        'allExamsCount' => $myExams, // Admins also only see their own
                        'myFilesCount' => $myFiles,
                        'allFilesCount' => $myFiles, // Admins also only see their own
                        'systemDetail' => Detail::all(),
                        'showPasswordReminder' => !Auth::user()->password_changed,
                    ]);
                }
            }else{
                $view->with([
                    'systemDetail' => Detail::all(),
                ]);
            }

        });
    }

    /**
     * Configure authentication redirects for clean URL structure
     */
    private function configureAuthenticationRedirects(): void
    {
        // Configure Laravel's default authentication redirects
        // Use URL instead of route() to avoid circular dependency during boot
        URL::defaults(['login' => '/login']);
    }
}
