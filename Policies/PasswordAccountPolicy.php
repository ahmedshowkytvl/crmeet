<?php

namespace App\Policies;

use App\Models\PasswordAccount;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PasswordAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Software developers and admins can view all accounts
        if ($user->role && in_array($user->role->slug, ['software_developer', 'admin', 'ceo'])) {
            return true;
        }

        // Other users can only view assigned accounts
        return $user->passwordAssignments()->active()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PasswordAccount $passwordAccount): bool
    {
        // Software developers and admins can view all accounts
        if ($user->role && in_array($user->role->slug, ['software_developer', 'admin', 'ceo'])) {
            return true;
        }

        // Check if user is assigned to this account
        return $passwordAccount->assignments()
                              ->where('user_id', $user->id)
                              ->whereNull('revoked_at')
                              ->exists();
    }

    /**
     * Determine whether the user can view the password.
     */
    public function viewPassword(User $user, PasswordAccount $passwordAccount): bool
    {
        // Software developers and admins can view all passwords
        if ($user->role && in_array($user->role->slug, ['software_developer', 'admin', 'ceo'])) {
            return true;
        }

        // Check if user is assigned to this account and can view password
        $assignment = $passwordAccount->assignments()
                                    ->where('user_id', $user->id)
                                    ->whereNull('revoked_at')
                                    ->first();

        return $assignment && $assignment->can_view_password;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only software developers, admins, and managers can create accounts
        return $user->role && in_array($user->role->slug, [
            'software_developer', 
            'admin', 
            'ceo', 
            'head_manager',
            'manager'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PasswordAccount $passwordAccount): bool
    {
        // Software developers and admins can update all accounts
        if ($user->role && in_array($user->role->slug, ['software_developer', 'admin', 'ceo'])) {
            return true;
        }

        // Check if user is assigned to this account and can edit
        $assignment = $passwordAccount->assignments()
                                    ->where('user_id', $user->id)
                                    ->whereNull('revoked_at')
                                    ->first();

        return $assignment && $assignment->can_edit_password;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PasswordAccount $passwordAccount): bool
    {
        // Only software developers and admins can delete accounts
        if ($user->role && in_array($user->role->slug, ['software_developer', 'admin', 'ceo'])) {
            return true;
        }

        // Check if user is assigned to this account and can delete
        $assignment = $passwordAccount->assignments()
                                    ->where('user_id', $user->id)
                                    ->whereNull('revoked_at')
                                    ->first();

        return $assignment && $assignment->can_delete_account;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PasswordAccount $passwordAccount): bool
    {
        // Only software developers and admins can restore accounts
        return $user->role && in_array($user->role->slug, ['software_developer', 'admin', 'ceo']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PasswordAccount $passwordAccount): bool
    {
        // Only software developers and admins can force delete accounts
        return $user->role && in_array($user->role->slug, ['software_developer', 'admin', 'ceo']);
    }

    /**
     * Determine whether the user can assign accounts to other users.
     */
    public function assign(User $user): bool
    {
        // Only software developers, admins, and managers can assign accounts
        return $user->role && in_array($user->role->slug, [
            'software_developer', 
            'admin', 
            'ceo', 
            'head_manager',
            'manager'
        ]);
    }

    /**
     * Determine whether the user can manage account assignments.
     */
    public function manageAssignments(User $user, PasswordAccount $passwordAccount): bool
    {
        // Software developers and admins can manage all assignments
        if ($user->role && in_array($user->role->slug, ['software_developer', 'admin', 'ceo'])) {
            return true;
        }

        // Managers can manage assignments for accounts they created
        return $passwordAccount->created_by === $user->id;
    }
}