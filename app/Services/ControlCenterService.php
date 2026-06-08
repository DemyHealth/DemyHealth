<?php

namespace App\Services;

final class ControlCenterService
{
    /** @return array<string,array<string,int>> */
    public function cards(InventoryService $inventory, EquipmentService $equipment, StaffService $staff, AuditLog $auditLog): array
    {
        return [
            'Inventory' => [
                'low stock items' => $this->countAlerts($inventory->alerts, 'low_stock'),
                'critical stock items' => $this->countAlerts($inventory->alerts, 'critical_stock'),
                'expiring reagents' => $this->countAlerts($inventory->alerts, 'expired'),
                'service readiness alerts' => count($inventory->alerts),
            ],
            'Equipment' => [
                'equipment due maintenance' => $this->countAlerts($equipment->alerts, 'due_maintenance'),
                'equipment due calibration' => $this->countAlerts($equipment->alerts, 'due_calibration'),
                'equipment down' => $this->countAlerts($equipment->alerts, 'equipment_down'),
                'branch equipment readiness' => count($equipment->equipment),
            ],
            'Staff' => [
                'active staff' => count(array_filter($staff->profiles, fn ($profile) => $profile['status'] === 'active')),
                'staff by branch' => count(array_unique(array_column($staff->profiles, 'branch_code'))),
                'training expiring' => count(array_filter($staff->trainings, fn ($training) => $training['status'] === 'expiring')),
                'documents expiring' => count(array_filter($staff->documents, fn ($document) => ($document['status'] ?? null) === 'expiring')),
            ],
            'Governance' => [
                'pending financial corrections' => $this->countAudit($auditLog, 'financial.payment_amount_corrected'),
                'pending refunds' => 0,
                'pending discounts' => 0,
                'pending booking deletions' => $this->countAudit($auditLog, 'booking.soft_deleted'),
            ],
        ];
    }

    /** @param array<int,array<string,mixed>> $alerts */
    private function countAlerts(array $alerts, string $type): int
    {
        return count(array_filter($alerts, fn ($alert) => $alert['alert_type'] === $type));
    }

    private function countAudit(AuditLog $auditLog, string $action): int
    {
        return count(array_filter($auditLog->entries(), fn ($entry) => $entry['action'] === $action));
    }
}
