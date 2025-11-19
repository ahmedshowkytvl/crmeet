<?php

namespace App\Policies;

use App\Models\ScheduleEvent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ScheduleEventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own events
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScheduleEvent $scheduleEvent): bool
    {
        // User can view if they own it, are an attendee, or are admin
        try {
            return $scheduleEvent->user_id === $user->id 
                || $scheduleEvent->isAttendee($user->id)
                || ($user->hasPermission && $user->hasPermission('manage-events'));
        } catch (\Exception $e) {
            // Fallback if hasPermission doesn't exist
            return $scheduleEvent->user_id === $user->id 
                || $scheduleEvent->isAttendee($user->id);
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create events
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScheduleEvent $scheduleEvent): bool
    {
        // User can update if they own it, are an organizer, or are admin
        try {
            return $scheduleEvent->canEdit($user->id) 
                || ($user->hasPermission && $user->hasPermission('manage-events'));
        } catch (\Exception $e) {
            return $scheduleEvent->canEdit($user->id);
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScheduleEvent $scheduleEvent): bool
    {
        // User can delete if they own it or are admin
        try {
            return $scheduleEvent->isOwner($user->id) 
                || ($user->hasPermission && $user->hasPermission('manage-events'));
        } catch (\Exception $e) {
            return $scheduleEvent->isOwner($user->id);
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ScheduleEvent $scheduleEvent): bool
    {
        try {
            return $scheduleEvent->isOwner($user->id) 
                || ($user->hasPermission && $user->hasPermission('manage-events'));
        } catch (\Exception $e) {
            return $scheduleEvent->isOwner($user->id);
        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ScheduleEvent $scheduleEvent): bool
    {
        try {
            return $user->hasPermission && $user->hasPermission('manage-events');
        } catch (\Exception $e) {
            return false;
        }
    }
}
