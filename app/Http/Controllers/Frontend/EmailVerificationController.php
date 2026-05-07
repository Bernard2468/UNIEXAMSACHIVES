<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ResendMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    private const OTP_TTL_MINUTES = 10;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAX_ATTEMPTS = 5;

    /**
     * Show the OTP entry page. Email is carried in the session so the user
     * cannot tamper with it via the URL.
     */
    public function show(Request $request)
    {
        $email = $request->session()->get('verify_email');

        if (! $email) {
            return redirect()->route('frontend.login')
                ->with('error', 'Verification session expired. Please log in or register again.');
        }

        return view('frontend.pages.verify-email', ['email' => $email]);
    }

    /**
     * Handle OTP submission.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $email = $request->session()->get('verify_email');

        if (! $email) {
            return redirect()->route('frontend.login')
                ->with('error', 'Verification session expired. Please log in or register again.');
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $request->session()->forget('verify_email');
            return redirect()->route('frontend.login')
                ->with('error', 'We could not find that account. Please register again.');
        }

        if ($user->hasVerifiedEmail()) {
            $request->session()->forget('verify_email');
            return redirect()->route('frontend.login')
                ->with('success', 'Your email is already verified. You can log in once approved.');
        }

        if ($user->email_otp_attempts >= self::MAX_ATTEMPTS) {
            $user->forceFill([
                'email_otp' => null,
                'email_otp_expires_at' => null,
            ])->save();

            return redirect()->route('verification.show')
                ->with('error', 'Too many incorrect attempts. Please request a new code.');
        }

        if ($user->isOtpExpired() || ! $user->email_otp) {
            return redirect()->route('verification.show')
                ->with('error', 'Your code has expired. Please request a new one.');
        }

        if (! Hash::check($request->input('otp'), $user->email_otp)) {
            $user->increment('email_otp_attempts');
            $remaining = max(0, self::MAX_ATTEMPTS - $user->email_otp_attempts);

            return redirect()->route('verification.show')
                ->with('error', "Incorrect code. {$remaining} attempt(s) remaining.");
        }

        $user->markEmailVerified();
        $request->session()->forget('verify_email');

        return redirect()->route('frontend.login')
            ->with('success', 'Email verified successfully! Please wait while your account is approved by an administrator.');
    }

    /**
     * Resend a fresh OTP, throttled by RESEND_COOLDOWN_SECONDS.
     */
    public function resend(Request $request)
    {
        $email = $request->session()->get('verify_email');

        if (! $email) {
            return redirect()->route('frontend.login')
                ->with('error', 'Verification session expired. Please log in or register again.');
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('frontend.login')
                ->with('error', 'We could not find that account.');
        }

        if ($user->hasVerifiedEmail()) {
            $request->session()->forget('verify_email');
            return redirect()->route('frontend.login')
                ->with('success', 'Your email is already verified.');
        }

        if ($user->email_otp_last_sent_at
            && $user->email_otp_last_sent_at->diffInSeconds(now()) < self::RESEND_COOLDOWN_SECONDS) {
            $wait = self::RESEND_COOLDOWN_SECONDS - $user->email_otp_last_sent_at->diffInSeconds(now());
            return redirect()->route('verification.show')
                ->with('error', "Please wait {$wait} second(s) before requesting another code.");
        }

        self::sendOtp($user);

        return redirect()->route('verification.show')
            ->with('success', 'A new verification code has been sent to your email.');
    }

    /**
     * Generate, hash, store, and email a fresh OTP. Reusable from registration flow.
     */
    public static function sendOtp(User $user): void
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'email_otp' => Hash::make($otp),
            'email_otp_expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            'email_otp_attempts' => 0,
            'email_otp_last_sent_at' => now(),
        ])->save();

        try {
            $resendService = new ResendMailService();
            $htmlContent = view('mails.email-otp', [
                'firstname' => $user->first_name,
                'email' => $user->email,
                'otp' => $otp,
                'expiresInMinutes' => self::OTP_TTL_MINUTES,
            ])->render();

            $response = $resendService->sendEmail(
                $user->email,
                'Your verification code: ' . $otp,
                $htmlContent,
                config('mail.from.address')
            );

            if (empty($response['success'])) {
                Log::error('Failed to send OTP email', [
                    'user_email' => $user->email,
                    'error' => $response['error'] ?? 'Unknown error',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending OTP email', [
                'user_email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
