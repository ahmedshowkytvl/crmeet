<?php

namespace App\Policies;

use App\Models\MeetingRoom;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeetingRoomPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All users can view meeting rooms
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MeetingRoom $meetingRoom): bool
    {
        return true; // All users can view meeting rooms
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins or users with permission can create rooms
        try {
            return $user->hasPermission && $user->hasPermission('manage-meeting-rooms');
        } catch (\Exception $e) {
            // Fallback: allow if user is admin or has specific role
            return $user->user_type === 'admin' || $user->role_id === 1;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MeetingRoom $meetingRoom): bool
    {
        // Only admins or users with permission can update rooms
        try {
            return $user->hasPermission && $user->hasPermission('manage-meeting-rooms');
        } catch (\Exception $e) {
            return $user->user_type === 'admin' || $user->role_id === 1;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MeetingRoom $meetingRoom): bool
    {
        // Only admins or users with permission can delete rooms
        try {
            return $user->hasPermission && $user->hasPermission('manage-meeting-rooms');
        } catch (\Exception $e) {
            return $user->user_type === 'admin' || $user->role_id === 1;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MeetingRoom $meetingRoom): bool
    {
        try {
            return $user->hasPermission && $user->hasPermission('manage-meeting-rooms');
        } catch (\Exception $e) {
            return $user->user_type === 'admin' || $user->role_id === 1;
        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MeetingRoom $meetingRoom): bool
    {
        try {
            return $user->hasPermission && $user->hasPermission('manage-meeting-rooms');
        } catch (\Exception $e) {
            return $user->user_type === 'admin' || $user->role_id === 1;
        }
    }
}
