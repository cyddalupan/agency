<?php

namespace App\Policies;

use App\Models\Agency;
use App\Models\User;

class AgencyPolicy
{
    /**
     * Perform pre-authorisation checks for super_admin.
     */
    public function before(User $currentUser, string $ability): bool|null
    {
        if ($currentUser->user_type === 'super_admin') {
            return true;
        }

        if (! in_array($currentUser->user_type, ['admin', 'super_admin'])) {
            return false;
        }

        return null;
    }

    /**
     * Determine whether the user can view any agencies.
     */
    public function viewAny(User $currentUser): bool
    {
        return $currentUser->user_type === 'admin';
    }

    /**
     * Determine whether the user can create agencies.
     */
    public function create(User $currentUser): bool
    {
        return $currentUser->user_type === 'admin';
    }

    /**
     * Determine whether the user can update an agency.
     */
    public function update(User $currentUser, Agency $agency): bool
    {
        return $currentUser->user_type === 'admin';
    }

    /**
     * Determine whether the user can deactivate an agency.
     */
    public function deactivate(User $currentUser, Agency $agency): bool
    {
        return $currentUser->user_type === 'admin';
    }

    /**
     * Determine whether the user can activate an agency.
     */
    public function activate(User $currentUser, Agency $agency): bool
    {
        return $currentUser->user_type === 'admin';
    }

    /**
     * Determine whether the user can manage branding for an agency.
     */
    public function branding(User $currentUser, Agency $agency): bool
    {
        return $currentUser->user_type === 'admin';
    }
}
