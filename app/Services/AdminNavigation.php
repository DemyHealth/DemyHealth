<?php

namespace App\Services;

use App\Models\User;
use App\Policies\AdminModulePolicy;
use App\Support\Roles;

final class AdminNavigation
{
    public function __construct(private AdminModulePolicy $policy = new AdminModulePolicy()) {}

    /** @return array<int,string> */
    public function linksFor(User $user): array
    {
        $links = [];
        if (in_array($user->role, [Roles::SUPER_ADMIN, Roles::ADMIN, Roles::FINANCE], true)) {
            $links[] = 'Governance';
        }
        if ($this->policy->canInventory($user)) {
            $links[] = 'Inventory';
        }
        if ($this->policy->canEquipment($user)) {
            $links[] = 'Equipment';
        }
        if ($this->policy->canStaff($user)) {
            $links[] = 'Staff / HR';
        }

        return $links;
    }
}
