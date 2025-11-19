<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load(['department', 'manager', 'subordinates', 'assignedTasks', 'employeeRequests']);
        
        return view('profile.index', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        $departments = \App\Models\Department::all();
        $users = User::where('id', '!=', $user->id)->get();
        
        return view('profile.edit', compact('user', 'departments', 'users'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone_work' => 'nullable|string|max:20',
            'phone_personal' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email|max:255',
            'job_title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'office_address' => 'nullable|string|max:500',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'birthday' => 'nullable|date',
            'bio' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user->update($request->all());

        return redirect()->route('profile.index')
            ->with('success', __('messages.profile_updated_successfully'));
    }

    public function changePassword()
    {
        return view('profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => __('messages.current_password_incorrect')]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.index')
            ->with('success', __('messages.password_updated_successfully'));
    }
}
