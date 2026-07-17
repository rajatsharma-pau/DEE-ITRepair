<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'phone';
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ], [
            'phone.required' => 'Please enter your mobile number.',
        ]);
    }

    protected function credentials(Request $request)
    {
        $phone = preg_replace('/\D+/', '', $request->get('phone'));
        if (strlen($phone) > 10 && substr($phone, 0, 2) === '91') {
            $phone = substr($phone, -10);
        }

        $credentials = [
            'phone' => $phone,
            'password' => $request->get('password'),
        ];

        if (\Schema::hasColumn('users', 'is_active')) {
            $credentials['is_active'] = 1;
        }

        return $credentials;
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'phone' => [trans('auth.failed')],
        ]);
    }
}
