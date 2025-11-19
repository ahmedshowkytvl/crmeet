<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        return view('settings.index', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'language' => 'required|in:en,ar',
            'timezone' => 'required|string|max:50',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:10',
            'notifications' => 'nullable|array',
            'notifications.email' => 'nullable|boolean',
            'notifications.sms' => 'nullable|boolean',
            'notifications.push' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        
        // Update user preferences
        $user->update([
            'language' => $request->language,
            'timezone' => $request->timezone,
            'date_format' => $request->date_format,
            'time_format' => $request->time_format,
            'notification_preferences' => json_encode($request->notifications ?? []),
        ]);

        // Set locale for current session
        app()->setLocale($request->language);

        return redirect()->route('settings.index')
            ->with('success', __('messages.settings_updated_successfully'));
    }

    public function notifications()
    {
        $user = Auth::user();
        $notifications = json_decode($user->notification_preferences ?? '{}', true);
        
        return view('settings.notifications', compact('user', 'notifications'));
    }

    public function updateNotifications(Request $request)
    {
        $request->validate([
            'notifications' => 'nullable|array',
            'notifications.email' => 'nullable|boolean',
            'notifications.sms' => 'nullable|boolean',
            'notifications.push' => 'nullable|boolean',
            'notifications.task_assignments' => 'nullable|boolean',
            'notifications.task_updates' => 'nullable|boolean',
            'notifications.request_updates' => 'nullable|boolean',
            'notifications.system_updates' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $user->update([
            'notification_preferences' => json_encode($request->notifications ?? []),
        ]);

        return redirect()->route('settings.notifications')
            ->with('success', __('messages.notification_settings_updated'));
    }
}