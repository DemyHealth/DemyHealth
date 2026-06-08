<?php

namespace App\Models;

use App\Support\Roles;
use InvalidArgumentException;

class User
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role = Roles::ADMIN,
        public string $passwordHash = '',
    ) {
        if (! Roles::isValid($this->role)) {
            throw new InvalidArgumentException("Unsupported role [{$this->role}].");
        }
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === Roles::SUPER_ADMIN;
    }
}
