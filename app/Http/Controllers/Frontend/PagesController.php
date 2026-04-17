<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Exam;
use App\Models\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Registration;
use App\Services\ResendMailService;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Mail\PasswordResetConfirmation;

class PagesController extends Controller
{
    // New welcome/landing page method
    public function welcome(Request $request){
        $currentDate = Carbon::now()->toDateString();
        $ipAddress = $request->ip();
        Visit::firstOrCreate(['visited_at' => $currentDate,  'ip_address' => $ipAddress]);
        
        // Gather real statistics from the system
        $stats = [
            'total_exams' => Exam::count(),
            'total_files' => File::count(),
            'total_departments' => Department::count(),
            'total_users' => User::where('is_approve', true)->count(), // Only count approved users
            'total_visits' => Visit::count(), // Total site visits
        ];
        
        return view('frontend.pages.welcome', compact('stats'));
    }

    // Keep the old index method for backward compatibility (if needed)
    public function index(Request $request){
        return $this->welcome($request);
    }

    public function about(){
        return view('frontend.pages.about');
    }

    public function news(){
        return view('frontend.pages.news');
    }

    public function leaderships(){
        return view('frontend.pages.leadership');
    }

    public function faqs(){
        return view('frontend.pages.faqs');
    }

    public function login(){
        // Redirect authenticated users to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        return view('frontend.pages.login', compact('departments', 'positions'));
    }

    public function adminLogin(){
        // Redirect authenticated users to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        $departments = Department::orderBy('name')->get();
        return view('frontend.pages.admin_login', compact('departments'));
    }

    public function adminLoginUser(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !password_verify($credentials['password'], $user->password)) {
            return redirect()->back()->with('error', 'Invalid email or password');
        }

        // Only allow non-admin users (advance communication users) to login through admin route
        if ($user->is_admin) {
            return redirect()->back()->with('error', 'This login portal is only for advance communication system users. Please use the regular login.');
        }

        if (!$user->is_approve) {
            return redirect()->back()->with('error', 'Your account is not yet approved.');
        }

        Auth::login($user);
        
        // Redirect to password change if using temporary password
        if (!$user->password_changed) {
            return redirect()->route('dashboard.settings')->with('warning', 'Please change your temporary password to continue.');
        }
        
        return redirect()->route('dashboard')->with('success', 'Login successful');
    }

    public function adminAccessRequest(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
            'reason' => 'required|string|min:20|max:1000',
            'supervisor' => 'nullable|string|max:255',
            'supervisor_email' => 'nullable|email|max:255',
        ]);

        // Verify the user's credentials
        $user = User::where('email', $validatedData['email'])->first();
        
        if (!$user || !password_verify($validatedData['password'], $user->password)) {
            return redirect()->back()->with('error', 'Invalid email or password. Please use your existing user account credentials.');
        }

        // Check if user is NOT an admin (normal users already have access to advance communication)
        if (!$user->is_admin) {
            return redirect()->back()->with('error', 'Normal users already have access to the advance communication system. This request form is for admin users requesting access to the advance communication system.');
        }

        // Check if user has already made a request
        if ($user->admin_access_requested) {
            return redirect()->back()->with('error', 'You have already submitted a request for access to the advance communication system. Please wait for approval.');
        }

        // Store the access request (you'll need to create a table for this)
        // For now, we'll just mark the user as having requested access
        $user->update([
            'admin_access_requested' => true,
            'admin_access_reason' => $validatedData['reason'],
            'admin_access_supervisor' => $validatedData['supervisor'],
            'admin_access_supervisor_email' => $validatedData['supervisor_email'],
            'admin_access_requested_at' => now(),
        ]);

        // Send notification to administrators (you can implement this later)
        // For now, just show success message
        
        return redirect()->back()->with('success', 'Your request for access to the advance communication system has been submitted successfully. You will be notified via email once a decision is made.');
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'department_id' => 'nullable|exists:departments,id',
            'staff_category' => 'required|string|in:Junior Staff,Senior Staff,Senior Member (Non-Teaching),Senior Member (Teaching)',
            'position_id' => 'nullable|exists:positions,id',
        ]);

        User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'is_admin' => 1,
            'is_approve' => false,
            'password_changed' => false,
            'password' => Hash::make($validatedData['password']),
            'department_id' => $validatedData['department_id'],
            'staff_category' => $validatedData['staff_category'],
            'position_id' => $validatedData['position_id'] ?? null,
        ]);

        // Send registration confirmation email to user (always attempt send)
        try {
            $resendService = new ResendMailService();
            
            $htmlContent = view('mails.registration', [
                'firstname' => $validatedData['first_name'],
                'email' => $validatedData['email']
            ])->render();
            
            \Log::info('Attempting to send registration email', [
                'user_email' => $validatedData['email'],
                'user_name' => $validatedData['first_name']
            ]);
            
            $response = $resendService->sendEmail(
                $validatedData['email'],
                'Registration Successful - Awaiting Approval',
                $htmlContent,
                config('mail.from.address')
            );
            
            if (!empty($response['success'])) {
                \Log::info('Registration email sent successfully', [
                    'user_email' => $validatedData['email'],
                    'message_id' => $response['message_id'] ?? 'N/A'
                ]);
            } else {
                \Log::error('Failed to send registration email', [
                    'user_email' => $validatedData['email'],
                    'error' => $response['error'] ?? 'Unknown error',
                    'response' => $response
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending registration email', [
                'user_email' => $validatedData['email'],
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('frontend.login')->with('success', 'Registration successful! Please wait while your account is being approved.');
    }

    public function loginUser(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !password_verify($credentials['password'], $user->password)) {
            return redirect()->back()->with('error', 'Invalid email or password');
        }

        // Block advance communication users from using regular login
        if (!$user->is_admin) {
            return redirect()->back()->with('error', 'Advance communication system users must use the admin portal at /admin. Please use that login instead.');
        }

        if ($user->is_admin && !$user->is_approve) {
            return redirect()->back()->with('error', 'Your account is not yet approved.');
        }

        Auth::login($user);
        
        // Redirect to password change if using temporary password
        if (!$user->password_changed) {
            return redirect()->route('dashboard.settings')->with('warning', 'Please change your temporary password to continue.');
        }
        
        return redirect()->route('dashboard')->with('success', 'Login successful');
    }

    // Password Reset Methods
    public function forgotPassword(Request $request)
    {
        $context = $request->query('context', 'default');
        $defaultEmail = $request->query('email', '');
        $redirectRoute = $context === 'super-admin' ? 'super-admin.login' : 'frontend.login';

        return view('frontend.pages.forgot-password', [
            'context' => $context,
            'defaultEmail' => $defaultEmail,
            'redirectRoute' => $redirectRoute,
        ]);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'redirect_to' => 'nullable|string',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            $redirectRoute = $request->input('redirect_to');

            if ($redirectRoute && in_array($redirectRoute, ['frontend.login', 'super-admin.login'], true)) {
                return redirect()->route($redirectRoute)->with('success', __($status));
            }

            return redirect()->back()->with('success', __($status));
        }

        return redirect()
            ->back()
            ->withErrors(['email' => __($status)])
            ->withInput($request->only('email'));
    }

    public function resetPassword($token)
    {
        return view('frontend.pages.reset-password', ['token' => $token]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                // Send password reset confirmation email
                $this->sendPasswordResetConfirmation($user);
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('frontend.login')->with('success', 'Password has been reset successfully! A confirmation email has been sent to your email address.')
                    : redirect()->back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Send password reset confirmation email
     */
    private function sendPasswordResetConfirmation($user)
    {
        try {
            if (env('MAIL_MAILER') == 'resend') {
                $resendService = new ResendMailService();
                
                $htmlContent = view('mails.password-reset-confirmation', [
                    'user' => $user,
                    'timestamp' => now()->format('F j, Y \a\t g:i A')
                ])->render();
                
                \Log::info('Attempting to send password reset confirmation email', [
                    'user_email' => $user->email,
                    'user_name' => $user->first_name
                ]);
                
                $response = $resendService->sendEmail(
                    $user->email,
                    'Password Reset Confirmation - University Digital Transformation Suite (UDTS)',
                    $htmlContent,
                    'cug@academicdigital.space'
                );
                
                if ($response['success']) {
                    \Log::info('Password reset confirmation email sent successfully', [
                        'user_email' => $user->email,
                        'message_id' => $response['message_id'] ?? 'N/A'
                    ]);
                } else {
                    \Log::error('Failed to send password reset confirmation email', [
                        'user_email' => $user->email,
                        'error' => $response['error'] ?? 'Unknown error',
                        'response' => $response
                    ]);
                }
            } else {
                // Fallback to Laravel's default mail system
                Mail::to($user->email)->send(new PasswordResetConfirmation($user));
                
                \Log::info('Password reset confirmation email sent via Laravel Mail', [
                    'user_email' => $user->email,
                    'user_name' => $user->first_name
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending password reset confirmation email', [
                'user_email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }
}
