<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Roles;

class AdminModulePolicy
{
    public function canInventory(User $user): bool
    {
        return in_array($user->role, [Roles::SUPER_ADMIN, Roles::ADMIN, Roles::LAB_MANAGER, Roles::LAB_STAFF, Roles::FINANCE], true);
    }

    public function canManageInventory(User $user): bool
    {
        return in_array($user->role, [Roles::SUPER_ADMIN, Roles::ADMIN, Roles::LAB_MANAGER], true);
    }

    public function canDeleteInventory(User $user): bool
    {
        return $user->role === Roles::SUPER_ADMIN;
    }

    public function canEquipment(User $user): bool
    {
        return in_array($user->role, [Roles::SUPER_ADMIN, Roles::ADMIN, Roles::LAB_MANAGER, Roles::LAB_STAFF, Roles::FINANCE], true);
    }

    public function canStaff(User $user): bool
    {
        return in_array($user->role, [Roles::SUPER_ADMIN, Roles::ADMIN, Roles::LAB_MANAGER, Roles::FINANCE], true);
    }

    public function canManageStaff(User $user): bool
    {
        return in_array($user->role, [Roles::SUPER_ADMIN, Roles::ADMIN], true);
    }
}
