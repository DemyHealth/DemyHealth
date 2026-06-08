<?php

namespace App\Services;

use App\Models\User;
use App\Policies\AdminModulePolicy;
use App\Support\Roles;
use RuntimeException;

final class EquipmentService
{
    public const STATUSES = ['active', 'due_maintenance', 'due_calibration', 'under_repair', 'down', 'retired'];
    public const EVENT_TYPES = ['maintenance', 'calibration', 'repair', 'downtime', 'service', 'inspection', 'validation'];

    /** @var array<int,array<string,mixed>> */
    public array $equipment = [];
    /** @var array<int,array<string,mixed>> */
    public array $events = [];
    /** @var array<int,array<string,mixed>> */
    public array $alerts = [];

    public function __construct(private AuditLog $auditLog, private AdminModulePolicy $policy = new AdminModulePolicy()) {}

    /** @param array<string,mixed> $attributes */
    public function register(User $actor, array $attributes): array
    {
        if (! $this->policy->canEquipment($actor) || $actor->role === Roles::LAB_STAFF) {
            throw new RuntimeException('Equipment registration denied.');
        }
        $this->assertBranch($attributes['branch_code']);
        $equipment = array_merge(['id' => count($this->equipment) + 1, 'status' => 'active'], $attributes);
        $this->equipment[$equipment['id']] = $equipment;
        $this->auditLog->record('equipment.registered', $actor, ['equipment_id' => $equipment['id']]);
        $this->evaluateAlerts($equipment);

        return $equipment;
    }

    public function addEvent(User $actor, int $equipmentId, string $eventType, string $eventDate, ?string $nextDueDate, string $notes = ''): array
    {
        if (! $this->policy->canEquipment($actor) || ! in_array($eventType, self::EVENT_TYPES, true)) {
            throw new RuntimeException('Equipment event denied.');
        }
        $equipment = $this->equipment[$equipmentId] ?? throw new RuntimeException('Equipment not found.');
        $event = [
            'id' => count($this->events) + 1,
            'equipment_id' => $equipmentId,
            'event_type' => $eventType,
            'event_date' => $eventDate,
            'performed_by' => $actor->id,
            'vendor' => null,
            'notes' => $notes,
            'attachment_path' => null,
            'next_due_date' => $nextDueDate,
        ];
        $this->events[] = $event;
        if ($eventType === 'maintenance') {
            $equipment['last_maintenance_date'] = $eventDate;
            $equipment['next_maintenance_date'] = $nextDueDate;
        }
        if ($eventType === 'calibration') {
            $equipment['last_calibration_date'] = $eventDate;
            $equipment['next_calibration_date'] = $nextDueDate;
        }
        if ($eventType === 'downtime') {
            $equipment['status'] = 'down';
        }
        $this->equipment[$equipmentId] = $equipment;
        $this->auditLog->record('equipment.'.$eventType, $actor, $event);
        $this->evaluateAlerts($equipment);

        return $event;
    }

    public function readinessByService(string $serviceCategory): string
    {
        foreach ($this->equipment as $equipment) {
            if (($equipment['linked_service_category'] ?? null) === $serviceCategory && $equipment['status'] === 'down') {
                return 'not_ready';
            }
        }

        return 'ready';
    }

    /** @param array<string,mixed> $equipment */
    private function evaluateAlerts(array $equipment): void
    {
        foreach (['maintenance' => 'next_maintenance_date', 'calibration' => 'next_calibration_date'] as $type => $field) {
            if (! empty($equipment[$field]) && strtotime($equipment[$field]) <= strtotime('+7 days')) {
                $this->alerts[] = [
                    'id' => count($this->alerts) + 1,
                    'equipment_id' => $equipment['id'],
                    'alert_type' => 'due_'.$type,
                    'severity' => 'warning',
                    'message' => ucfirst($type).' due soon.',
                    'status' => 'open',
                    'resolved_by' => null,
                    'resolved_at' => null,
                ];
            }
        }
        if (($equipment['status'] ?? null) === 'down') {
            $this->alerts[] = [
                'id' => count($this->alerts) + 1,
                'equipment_id' => $equipment['id'],
                'alert_type' => 'equipment_down',
                'severity' => 'critical',
                'message' => 'Equipment is down and affects service readiness.',
                'status' => 'open',
                'resolved_by' => null,
                'resolved_at' => null,
            ];
        }
    }

    private function assertBranch(string $branchCode): void
    {
        if (! Roles::isOfficialBranch($branchCode)) {
            throw new RuntimeException('Invalid branch code.');
        }
    }
}
