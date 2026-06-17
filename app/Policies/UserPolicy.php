<?php

namespace App\Policies;

use App\Models\User;

/**
 * Authorisation policy for User CRUD operations.
 *
 * Every method enforces agency-scoping so that admin users
 * can only manage users belonging to their own agency.
 */
class UserPolicy
{
    /**
     * Perform pre-authorisation checks for super_admin.
     */
    public function before(User $currentUser, string $ability): bool|null
    {
        // super_admin can manage users across all agencies
        if ($currentUser->user_type === 'super_admin') {
            return true;
        }

        // Only admin-level users may manage other users
        if (! in_array($currentUser->user_type, ['admin', 'super_admin'])) {
            return false;
        }

        return null; // fall through to the method-specific check
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $currentUser): bool
    {
        return in_array($currentUser->user_type, ['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $currentUser, User $user): bool
    {
        return $this->inSameAgency($currentUser, $user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $currentUser): bool
    {
        return in_array($currentUser->user_type, ['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $currentUser, User $user): bool
    {
        return $this->inSameAgency($currentUser, $user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * Users cannot delete their own account.
     */
    public function delete(User $currentUser, User $user): bool
    {
        if ($currentUser->id === $user->id) {
            return false;
        }

        return $this->inSameAgency($currentUser, $user);
    }

    /**
     * Check whether both users belong to the same agency.
     */
    private function inSameAgency(User $currentUser, User $user): bool
    {
        // super_admin has no agency_id – allow access to any user
        if ($currentUser->user_type === 'super_admin') {
            return true;
        }

        return $currentUser->agency_id === $user->agency_id;
    }
}
