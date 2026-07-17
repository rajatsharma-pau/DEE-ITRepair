<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showForm()
    {
        return view('auth.passwords.change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ])->withInput();
        }

        $user->password = Hash::make($request->password);
        if (Schema_has_column('users', 'must_change_password')) {
            $user->must_change_password = 0;
        }
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Password changed successfully.');
    }
}

if (!function_exists('Schema_has_column')) {
    function Schema_has_column($table, $column)
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            return false;
        }
    }
}
