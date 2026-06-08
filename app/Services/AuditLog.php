<?php

namespace App\Services;

use App\Models\User;

final class AuditLog
{
    /** @var array<int,array<string,mixed>> */
    private array $entries = [];

    /** @param array<string,mixed> $metadata */
    public function record(string $action, User $actor, array $metadata = []): void
    {
        $this->entries[] = [
            'action' => $action,
            'actor_id' => $actor->id,
            'actor_role' => $actor->role,
            'metadata' => $metadata,
            'created_at' => date(DATE_ATOM),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public function entries(): array
    {
        return $this->entries;
    }

    public function contains(string $action): bool
    {
        foreach ($this->entries as $entry) {
            if ($entry['action'] === $action) {
                return true;
            }
        }

        return false;
    }
}
