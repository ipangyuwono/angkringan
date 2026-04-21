<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class OtpController extends Controller
{
    /**
     * Show OTP challenge page.
     * User is already logged in by Fortify, but otp_verified is not yet set.
     */
    public function show(Request $request): Response|BaseResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Already verified → skip OTP
        if ($request->session()->get('otp_verified')) {
            $user = auth()->user();
            return redirect()->route($user->role === 'admin' ? 'dashboard' : 'transaksi.index');
        }

        /** @var User $user */
        $user = auth()->user();

        return Inertia::render('auth/OtpChallenge', [
            'email'   => $this->getMaskedEmail($user),
            'otpCode' => $user->otp_code, // Send OTP from DB to view
            'status'  => $request->session()->get('status'),
        ]);
    }

    /**
     * Verify the submitted OTP code.
     */
    public function verify(Request $request): BaseResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        if (!auth()->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = auth()->user();

        // Check expired
        if (!$user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'Kode OTP sudah expired. Silakan kirim ulang.']);
        }

        // Check code
        if ($user->otp_code !== $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        // Clear OTP from DB
        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        // Mark OTP as verified in session
        $request->session()->put('otp_verified', true);
        $request->session()->forget(['login.id', 'login.remember']);

        return redirect()->intended(
            $user->role === 'admin' ? route('dashboard') : route('transaksi.index')
        );
    }

    /**
     * Resend OTP to the authenticated user.
     */
    public function resend(Request $request): BaseResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        /** @var User $user */
        $user = auth()->user();
        self::generateAndSendOtp($user);

        return back()->with('status', 'Kode OTP baru telah dikirim.');
    }

    /**
     * Generate a 6-digit OTP, save to user, and send via email.
     */
    public static function generateAndSendOtp(User $user): void
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code'       => $otp,
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));
    }

    /**
     * Mask an email address for display.
     */
    private function getMaskedEmail(User $user): string
    {
        $email = $user->email ?? '';
        $parts = explode('@', $email);

        if (count($parts) !== 2) return '***@***.***';

        $name   = $parts[0];
        $domain = $parts[1];
        $masked = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));

        return $masked . '@' . $domain;
    }
}
