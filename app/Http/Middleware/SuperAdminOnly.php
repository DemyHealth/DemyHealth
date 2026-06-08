<?php

namespace App\Http\Middleware;

use App\Models\User;
use RuntimeException;

class SuperAdminOnly
{
    public function authorize(User $user): void
    {
        if (! $user->isSuperAdmin()) {
            throw new RuntimeException('This action requires super_admin approval.');
        }
    }
}
