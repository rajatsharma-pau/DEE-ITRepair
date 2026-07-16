<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $user = Auth::user();
        $employee = $user->employee;

        return view('profile.show', compact('user', 'employee'));
    }

    public function updatePhoto(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->withErrors('Employee profile not found. Contact admin.');
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png|max:1024',
        ]);

        if ($employee->photo && Storage::disk('public')->exists(str_replace('public/', '', $employee->photo))) {
            Storage::disk('public')->delete(str_replace('public/', '', $employee->photo));
        }

        $path = $request->file('photo')->store('employee_photos', 'public');
        $employee->photo = $path;
        $employee->save();

        return redirect()->route('profile.show')->with('success', 'Profile photo updated successfully.');
    }

    public function removePhoto()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->withErrors('Employee profile not found. Contact admin.');
        }

        if ($employee->photo && Storage::disk('public')->exists(str_replace('public/', '', $employee->photo))) {
            Storage::disk('public')->delete(str_replace('public/', '', $employee->photo));
        }

        $employee->photo = null;
        $employee->save();

        return redirect()->route('profile.show')->with('success', 'Profile photo removed successfully.');
    }
}
