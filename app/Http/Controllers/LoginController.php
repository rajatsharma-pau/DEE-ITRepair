<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm(){ return view('auth.login'); }
    public function login(Request $request)
    {
        $request->validate(['phone'=>'required','password'=>'required']);
        if (Auth::attempt(['phone'=>$request->phone,'password'=>$request->password,'is_active'=>1], $request->filled('remember'))) {
            return redirect()->intended('/dashboard');
        }
        return back()->withErrors(['phone'=>'Invalid phone number or password.'])->withInput();
    }
    public function logout(Request $request){ Auth::logout(); return redirect('/login'); }
}
