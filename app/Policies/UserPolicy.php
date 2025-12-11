<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        
    }

    public function createUser(User $user): bool
    {
        Log::error('hi from policy - create user');
        return $user->isAdmin();
    }
    public function browseUsers(User $user): bool
    {
        Log::error('hi from policy - browse users');
        return $user->isAdmin();
    }
    public function createCategory(User $user): bool
    {
        Log::error('hi from policy - create category');
        return $user->isAdmin() || $user->isInventory();
    }
}
