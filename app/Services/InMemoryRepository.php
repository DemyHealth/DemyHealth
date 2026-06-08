<?php

namespace App\Services;

use App\Models\User;

final class InMemoryRepository
{
    /** @var array<string,User> */
    public array $usersByEmail = [];

    public function upsertUser(string $name, string $email, string $role, string $password): User
    {
        $existing = $this->usersByEmail[$email] ?? null;
        $id = $existing?->id ?? count($this->usersByEmail) + 1;
        $user = new User($id, $name, $email, $role, password_hash($password, PASSWORD_BCRYPT));
        $this->usersByEmail[$email] = $user;

        return $user;
    }

    public function user(string $email): ?User
    {
        return $this->usersByEmail[$email] ?? null;
    }
}
