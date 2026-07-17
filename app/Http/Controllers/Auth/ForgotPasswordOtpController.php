<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpSmsService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ForgotPasswordOtpController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-phone');
    }

    public function sendOtp(Request $request, OtpSmsService $smsService)
    {
        $request->validate([
            'phone' => 'required|digits_between:10,15',
        ]);

        $phone = preg_replace('/\D+/', '', $request->phone);

        $user = User::where('phone', $phone)->where('is_active', 1)->first();
        if (!$user) {
            return back()->withErrors(['phone' => 'No active account found with this phone number.'])->withInput();
        }

        $otp = (string) random_int(100000, 999999);

        $user->password_reset_otp = Hash::make($otp);
        $user->password_reset_otp_expires_at = Carbon::now()->addMinutes(10);
        $user->password_reset_otp_verified_at = null;
        $user->save();

        $sent = $smsService->sendOtp($phone, $otp);
        if (!$sent) {
            return back()->withErrors(['phone' => 'OTP could not be sent. Please try again or contact administrator.'])->withInput();
        }

        Session::put('password_reset_phone', $phone);

        return redirect()->route('password.otp.reset.form')->with('success', 'OTP has been sent to your registered phone number.');
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-otp', [
            'phone' => Session::get('password_reset_phone'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits_between:10,15',
            'otp' => 'required|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        $phone = preg_replace('/\D+/', '', $request->phone);
        $user = User::where('phone', $phone)->where('is_active', 1)->first();

        if (!$user || !$user->password_reset_otp) {
            return back()->withErrors(['otp' => 'Invalid OTP request. Please generate OTP again.'])->withInput();
        }

        if (!$user->password_reset_otp_expires_at || Carbon::parse($user->password_reset_otp_expires_at)->isPast()) {
            return back()->withErrors(['otp' => 'OTP has expired. Please generate OTP again.'])->withInput();
        }

        if (!Hash::check($request->otp, $user->password_reset_otp)) {
            return back()->withErrors(['otp' => 'Invalid OTP.'])->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->password_reset_otp = null;
        $user->password_reset_otp_expires_at = null;
        $user->password_reset_otp_verified_at = Carbon::now();
        $user->last_password_changed_at = Carbon::now();
        $user->must_change_password = 0;
        $user->save();

        Session::forget('password_reset_phone');

        return redirect()->route('login')->with('success', 'Password reset successfully. Please login with your new password.');
    }
}
