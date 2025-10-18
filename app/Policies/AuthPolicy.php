<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class AuthPolicy
{
    /**
     * Determine if the user can access guest-only routes (login, register).
     * Returns false if user is authenticated (deny access).
     * Returns true if user is null/unauthenticated (allow access).
     */
    public function guest(?User $user): bool
    {
        return $user === null;
    }
}
