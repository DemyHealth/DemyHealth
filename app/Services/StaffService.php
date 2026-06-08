<?php

namespace App\Services;

use App\Models\User;
use App\Policies\AdminModulePolicy;
use App\Support\Roles;
use RuntimeException;

final class StaffService
{
    public const DEPARTMENTS = ['Lab', 'Dispatch', 'Front Desk', 'Finance', 'Marketing', 'Admin', 'IT', 'Management'];

    /** @var array<int,array<string,mixed>> */
    public array $profiles = [];
    /** @var array<int,array<string,mixed>> */
    public array $documents = [];
    /** @var array<int,array<string,mixed>> */
    public array $trainings = [];

    public function __construct(private AuditLog $auditLog, private AdminModulePolicy $policy = new AdminModulePolicy()) {}

    /** @param array<string,mixed> $attributes */
    public function createProfile(User $actor, array $attributes): array
    {
        if (! $this->policy->canManageStaff($actor)) {
            throw new RuntimeException('Staff/HR management denied.');
        }
        $this->assertBranch($attributes['branch_code']);
        if (! in_array($attributes['department'], self::DEPARTMENTS, true)) {
            throw new RuntimeException('Unsupported department.');
        }
        $profile = array_merge(['id' => count($this->profiles) + 1, 'status' => 'active'], $attributes);
        $this->profiles[$profile['id']] = $profile;
        $this->auditLog->record('staff.profile_created', $actor, ['staff_profile_id' => $profile['id']]);

        return $profile;
    }

    public function addTraining(User $actor, int $profileId, string $title, string $expiresAt): array
    {
        if (! $this->policy->canStaff($actor)) {
            throw new RuntimeException('Training updates denied.');
        }
        $training = [
            'id' => count($this->trainings) + 1,
            'staff_profile_id' => $profileId,
            'training_title' => $title,
            'training_type' => 'compliance',
            'provider' => 'DemyHealth',
            'completed_at' => date('Y-m-d'),
            'expires_at' => $expiresAt,
            'certificate_path' => null,
            'status' => strtotime($expiresAt) <= strtotime('+30 days') ? 'expiring' : 'active',
        ];
        $this->trainings[] = $training;
        $this->auditLog->record('staff.training_updated', $actor, $training);

        return $training;
    }

    private function assertBranch(string $branchCode): void
    {
        if (! Roles::isOfficialBranch($branchCode)) {
            throw new RuntimeException('Staff branch assignment must use DHH, DHA, DHM, DHI, or ABA.');
        }
    }
}
