<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Validator;

class AnnouncementManagementController extends Controller
{
    /**
     * Display a listing of announcements
     */
    public function index()
    {
        $announcements = Announcement::with(['creator', 'event'])
            ->recent()
            ->paginate(10);

        return response()->json($announcements);
    }

    /**
     * Get announcements visible to current user
     */
    public function getVisibleAnnouncements()
    {
        $announcements = Announcement::with(['creator', 'event'])
            ->visibleToUser(auth()->id())
            ->recent()
            ->paginate(10);

        return response()->json($announcements);
    }

    /**
     * Store a newly created announcement
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_type' => 'required|in:all,selected',
            'target_ids' => 'required_if:target_type,selected|array',
            'target_ids.*' => 'exists:users,id',
            'attached_event_id' => 'nullable|exists:events,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['created_by'] = auth()->id();

        // If target_type is 'all', set target_ids to null
        if ($data['target_type'] === 'all') {
            $data['target_ids'] = null;
        }

        $announcement = Announcement::create($data);

        // TODO: Send notifications to target users
        $this->sendNotifications($announcement);

        return response()->json([
            'success' => true,
            'message' => 'Announcement created successfully',
            'announcement' => $announcement->load('creator', 'event')
        ]);
    }

    /**
     * Display the specified announcement
     */
    public function show(Announcement $announcement)
    {
        return response()->json($announcement->load('creator', 'event'));
    }

    /**
     * Update the specified announcement
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_type' => 'required|in:all,selected',
            'target_ids' => 'required_if:target_type,selected|array',
            'target_ids.*' => 'exists:users,id',
            'attached_event_id' => 'nullable|exists:events,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // If target_type is 'all', set target_ids to null
        if ($data['target_type'] === 'all') {
            $data['target_ids'] = null;
        }

        $announcement->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully',
            'announcement' => $announcement->load('creator', 'event')
        ]);
    }

    /**
     * Remove the specified announcement
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully'
        ]);
    }

    /**
     * Get users for target selection
     */
    public function getUsersForTarget()
    {
        $users = User::select('id', 'name', 'email')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    /**
     * Send notifications to target users
     */
    private function sendNotifications(Announcement $announcement)
    {
        // This is a placeholder for notification logic
        // You can implement email, push notifications, or in-app notifications here
        
        $targetUsers = [];
        
        if ($announcement->target_type === 'all') {
            $targetUsers = User::where('status', 'active')->get();
        } else {
            $targetUsers = User::whereIn('id', $announcement->target_ids)->get();
        }

        // TODO: Implement actual notification sending
        // For now, we'll just log the notification
        \Log::info('Announcement notification sent', [
            'announcement_id' => $announcement->id,
            'target_users_count' => $targetUsers->count()
        ]);
    }
}
