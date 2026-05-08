<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, User $targetUser): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, User $targetUser): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $this->isAdmin($user);
    }

    public function ban(User $user, User $targetUser): bool
    {
        return $this->isAdmin($user);
    }

    public function unban(User $user, User $targetUser): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }
}
