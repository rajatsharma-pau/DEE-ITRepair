<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DeeAuthController extends Controller
{
    public function showLogin(){ return view('auth.login'); }

    public function login(Request $request)
    {
        $request->validate(['phone'=>'required','password'=>'required']);
        $credentials = ['phone'=>$request->phone, 'password'=>$request->password, 'status'=>'active'];
        if (Auth::guard('dee')->attempt($credentials, $request->filled('remember'))) {
            return redirect()->route('dee.dashboard');
        }
        return back()->withErrors(['phone'=>'Invalid phone number or password.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('dee')->logout();
        return redirect()->route('dee.login');
    }

    public function changePasswordForm(){ return view('auth.change_password'); }
    public function changePassword(Request $request)
    {
        $request->validate(['password'=>'required|min:6|confirmed']);
        $user = Auth::guard('dee')->user();
        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();
        return redirect()->route('dee.dashboard')->with('success','Password changed successfully.');
    }
}
