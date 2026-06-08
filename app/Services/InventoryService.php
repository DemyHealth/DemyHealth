<?php

namespace App\Services;

use App\Models\User;
use App\Policies\AdminModulePolicy;
use App\Support\Roles;
use RuntimeException;

final class InventoryService
{
    public const CATEGORIES = [
        'DNA sample collection kits', 'PCR reagents', 'HPV reagents', 'STI reagents',
        'Viral load reagents', 'Sequencing consumables', 'Blood collection consumables',
        'Swabs', 'PPE', 'General lab consumables',
    ];

    public const MOVEMENT_TYPES = ['stock_in', 'stock_out', 'transfer', 'adjustment', 'expired', 'damaged', 'reserved', 'released'];

    /** @var array<int,array<string,mixed>> */
    public array $items = [];
    /** @var array<int,array<string,mixed>> */
    public array $movements = [];
    /** @var array<int,array<string,mixed>> */
    public array $alerts = [];

    public function __construct(private AuditLog $auditLog, private AdminModulePolicy $policy = new AdminModulePolicy()) {}

    /** @param array<string,mixed> $attributes */
    public function createItem(User $actor, array $attributes): array
    {
        if (! $this->policy->canManageInventory($actor)) {
            throw new RuntimeException('Inventory management denied.');
        }
        $this->assertBranch($attributes['branch_code']);
        $item = array_merge(['id' => count($this->items) + 1, 'status' => 'active', 'current_quantity' => 0], $attributes);
        $this->items[$item['id']] = $item;
        $this->auditLog->record('inventory.item_created', $actor, ['inventory_item_id' => $item['id']]);
        $this->evaluateAlerts($item);

        return $item;
    }

    public function deleteItem(User $actor, int $itemId): void
    {
        if (! $this->policy->canDeleteInventory($actor)) {
            throw new RuntimeException('Only super_admin can delete inventory items.');
        }
        unset($this->items[$itemId]);
        $this->auditLog->record('inventory.item_deleted', $actor, ['inventory_item_id' => $itemId]);
    }

    public function move(User $actor, int $itemId, string $type, int $quantity, string $branchCode, string $reason, ?User $approver = null): array
    {
        if (! $this->policy->canInventory($actor) || ! in_array($type, self::MOVEMENT_TYPES, true)) {
            throw new RuntimeException('Inventory movement denied.');
        }
        $this->assertBranch($branchCode);
        $item = $this->items[$itemId] ?? throw new RuntimeException('Inventory item not found.');
        $signedQuantity = in_array($type, ['stock_out', 'expired', 'damaged', 'reserved'], true) ? -$quantity : $quantity;
        if ($type === 'adjustment' && ! $approver?->isSuperAdmin()) {
            throw new RuntimeException('Inventory adjustments require super_admin approval.');
        }
        $item['current_quantity'] += $signedQuantity;
        $this->items[$itemId] = $item;
        $movement = [
            'id' => count($this->movements) + 1,
            'inventory_item_id' => $itemId,
            'movement_type' => $type,
            'quantity' => $quantity,
            'branch_code' => $branchCode,
            'reason' => $reason,
            'performed_by' => $actor->id,
            'approved_by' => $approver?->id,
            'reference_type' => null,
            'reference_id' => null,
            'created_at' => date(DATE_ATOM),
        ];
        $this->movements[] = $movement;
        $this->auditLog->record('inventory.'.$type, $actor, $movement);
        $this->evaluateAlerts($item);

        return $movement;
    }

    /** @param array<string,mixed> $item */
    private function evaluateAlerts(array $item): void
    {
        $quantity = (int) $item['current_quantity'];
        if ($quantity <= (int) $item['critical_level']) {
            $this->alerts[] = $this->alert($item, 'critical_stock', 'critical', 'Critical stock level reached.');
        } elseif ($quantity <= (int) $item['reorder_level']) {
            $this->alerts[] = $this->alert($item, 'low_stock', 'warning', 'Low stock level reached.');
        }
        if (! empty($item['expiry_date']) && strtotime($item['expiry_date']) <= time()) {
            $this->items[$item['id']]['status'] = 'expired';
            $this->alerts[] = $this->alert($item, 'expired', 'critical', 'Inventory item has expired.');
        }
    }

    /** @param array<string,mixed> $item @return array<string,mixed> */
    private function alert(array $item, string $type, string $severity, string $message): array
    {
        return [
            'id' => count($this->alerts) + 1,
            'inventory_item_id' => $item['id'],
            'alert_type' => $type,
            'branch_code' => $item['branch_code'],
            'service_category' => $item['linked_service_category'] ?? null,
            'message' => $message,
            'severity' => $severity,
            'status' => 'open',
            'resolved_by' => null,
            'resolved_at' => null,
        ];
    }

    private function assertBranch(string $branchCode): void
    {
        if (! Roles::isOfficialBranch($branchCode)) {
            throw new RuntimeException('Branch code must be one of DHH, DHA, DHM, DHI, ABA.');
        }
    }
}
