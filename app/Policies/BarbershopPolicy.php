<?php

namespace App\Policies;

use App\Models\Barbershop;
use App\Models\User;

class BarbershopPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Barbershop $barbershop): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Barbershop $barbershop): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Barbershop $barbershop): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }
}
