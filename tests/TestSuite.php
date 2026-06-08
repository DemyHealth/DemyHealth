<?php

namespace Tests;

use App\Models\User;
use App\Policies\AdminModulePolicy;
use App\Services\AuditLog;
use App\Services\ControlCenterService;
use App\Services\EquipmentService;
use App\Services\FinancialGovernanceService;
use App\Services\InventoryService;
use App\Services\StaffService;
use App\Support\Roles;
use Database\Seeders\DatabaseSeeder;
use RuntimeException;
use Throwable;

final class TestSuite
{
    private int $assertions = 0;

    public static function run(): int
    {
        $suite = new self();
        $tests = array_filter(get_class_methods($suite), fn ($method) => str_starts_with($method, 'test'));

        foreach ($tests as $test) {
            try {
                $suite->{$test}();
                echo "PASS {$test}\n";
            } catch (Throwable $throwable) {
                echo "FAIL {$test}: {$throwable->getMessage()}\n";

                return 1;
            }
        }

        echo "OK ({$suite->assertions} assertions)\n";

        return 0;
    }

    public function testSuperAdminSeederCreatesAndUpdatesSuperAdminUser(): void
    {
        $repository = (new DatabaseSeeder())->run();
        $user = $repository->user('superadmin@demyhealth.com');
        $this->assert($user !== null, 'Super admin user was not seeded.');
        $this->assert($user->role === Roles::SUPER_ADMIN, 'Seeded user must have super_admin role.');
    }

    public function testFinanceCannotEditPaymentAmountDirectly(): void
    {
        $audit = new AuditLog();
        $service = new FinancialGovernanceService($audit);
        $finance = new User(2, 'Finance', 'finance@example.test', Roles::FINANCE);
        $this->expectException(fn () => $service->correctPaymentAmount($finance, ['id' => 1, 'amount' => 100.00], 125.00, 'Correction'));
    }

    public function testSuperAdminCanApprovePaymentCorrectionAndAuditLogIsCreated(): void
    {
        $audit = new AuditLog();
        $service = new FinancialGovernanceService($audit);
        $super = new User(1, 'Super', 'super@example.test', Roles::SUPER_ADMIN);
        $payment = $service->correctPaymentAmount($super, ['id' => 1, 'amount' => 100.00], 125.00, 'Underpayment correction');
        $this->assert($payment['amount'] === 125.00, 'Payment amount was not corrected.');
        $this->assert($payment['original_values']['amount'] === 100.00, 'Original payment amount was not preserved.');
        $this->assert($audit->contains('financial.payment_amount_corrected'), 'Financial correction audit log missing.');
    }

    public function testAdminCannotDeleteBooking(): void
    {
        $service = new FinancialGovernanceService(new AuditLog());
        $admin = new User(3, 'Admin', 'admin@example.test', Roles::ADMIN);
        $this->expectException(fn () => $service->softDeleteBooking($admin, ['id' => 7], 'Duplicate'));
    }

    public function testMarketingDeniedFromSensitiveFinancialEdit(): void
    {
        $service = new FinancialGovernanceService(new AuditLog());
        $marketing = new User(4, 'Marketing', 'marketing@example.test', Roles::MARKETING);
        $this->expectException(fn () => $service->correctPaymentAmount($marketing, ['id' => 1, 'amount' => 100], 90, 'Correction'));
    }

    public function testSuperAdminCanSoftDeleteAndRestoreBookingWithReason(): void
    {
        $audit = new AuditLog();
        $service = new FinancialGovernanceService($audit);
        $super = new User(1, 'Super', 'super@example.test', Roles::SUPER_ADMIN);
        $deleted = $service->softDeleteBooking($super, ['id' => 7], 'Duplicate booking');
        $restored = $service->restoreBooking($super, $deleted, 'Deletion reversed after review');
        $this->assert($deleted['deletion_reason'] === 'Duplicate booking', 'Deletion reason missing.');
        $this->assert($restored['deleted_at'] === null, 'Booking was not restored.');
        $this->assert($audit->contains('booking.soft_deleted') && $audit->contains('booking.restored'), 'Booking governance audit logs missing.');
    }

    public function testInventoryItemCanBeCreated(): void
    {
        [$inventory, $super] = $this->inventoryFixture();
        $item = $inventory->createItem($super, $this->inventoryAttributes(['current_quantity' => 12]));
        $this->assert($item['name'] === 'PCR Reagent A', 'Inventory item was not created.');
    }

    public function testStockMovementUpdatesQuantityAndLowStockAlertTriggers(): void
    {
        [$inventory, $super] = $this->inventoryFixture();
        $item = $inventory->createItem($super, $this->inventoryAttributes(['current_quantity' => 12, 'reorder_level' => 10, 'critical_level' => 3]));
        $inventory->move($super, $item['id'], 'stock_out', 3, 'DHH', 'Used for PCR batch');
        $this->assert($inventory->items[$item['id']]['current_quantity'] === 9, 'Stock movement did not decrement quantity.');
        $this->assert($inventory->alerts[0]['alert_type'] === 'low_stock', 'Low stock alert was not created.');
    }

    public function testExpiredItemIsFlaggedLabStaffCannotDeleteAndSuperAdminApprovesAdjustment(): void
    {
        [$inventory, $super] = $this->inventoryFixture();
        $labStaff = new User(5, 'Lab Staff', 'lab@example.test', Roles::LAB_STAFF);
        $item = $inventory->createItem($super, $this->inventoryAttributes(['expiry_date' => '2026-01-01', 'current_quantity' => 5]));
        $this->assert($inventory->items[$item['id']]['status'] === 'expired', 'Expired item was not flagged.');
        $this->expectException(fn () => $inventory->deleteItem($labStaff, $item['id']));
        $movement = $inventory->move($labStaff, $item['id'], 'adjustment', 2, 'DHH', 'Count correction', $super);
        $this->assert($movement['approved_by'] === $super->id, 'Super admin approval was not captured for adjustment.');
    }

    public function testEquipmentCanBeRegisteredAndMaintenanceEventLogsCorrectly(): void
    {
        $audit = new AuditLog();
        $equipmentService = new EquipmentService($audit);
        $super = new User(1, 'Super', 'super@example.test', Roles::SUPER_ADMIN);
        $equipment = $equipmentService->register($super, $this->equipmentAttributes());
        $event = $equipmentService->addEvent($super, $equipment['id'], 'maintenance', '2026-06-08', '2026-09-08', 'Quarterly service');
        $this->assert($equipment['name'] === 'PCR Analyzer', 'Equipment was not registered.');
        $this->assert($event['next_due_date'] === '2026-09-08', 'Maintenance event next due date missing.');
        $this->assert($audit->contains('equipment.maintenance'), 'Maintenance audit log missing.');
    }

    public function testCalibrationAlertAppearsAndDownEquipmentAffectsServiceReadiness(): void
    {
        $equipmentService = new EquipmentService(new AuditLog());
        $super = new User(1, 'Super', 'super@example.test', Roles::SUPER_ADMIN);
        $equipment = $equipmentService->register($super, $this->equipmentAttributes(['next_calibration_date' => '2026-06-10']));
        $this->assert($equipmentService->alerts[0]['alert_type'] === 'due_calibration', 'Calibration alert missing.');
        $equipmentService->addEvent($super, $equipment['id'], 'downtime', '2026-06-08', null, 'Rotor failure');
        $this->assert($equipmentService->readinessByService('PCR') === 'not_ready', 'Down equipment did not affect readiness.');
    }

    public function testUnauthorizedRoleDeniedFromEquipment(): void
    {
        $equipmentService = new EquipmentService(new AuditLog());
        $marketing = new User(4, 'Marketing', 'marketing@example.test', Roles::MARKETING);
        $this->expectException(fn () => $equipmentService->register($marketing, $this->equipmentAttributes()));
    }

    public function testStaffProfileCanBeCreatedAndBranchUsesOfficialCodesAndUserCanBeLinked(): void
    {
        $staff = new StaffService(new AuditLog());
        $super = new User(1, 'Super', 'super@example.test', Roles::SUPER_ADMIN);
        $profile = $staff->createProfile($super, $this->staffAttributes(['user_id' => 99, 'branch_code' => 'ABA']));
        $this->assert($profile['user_id'] === 99, 'Staff profile was not linked to user.');
        $this->assert(Roles::isOfficialBranch($profile['branch_code']), 'Branch assignment is not an official branch code.');
    }

    public function testAdminCanCreateStaffProfileMarketingAndPartnerDeniedFromHrAndTrainingExpiryCreated(): void
    {
        $staff = new StaffService(new AuditLog());
        $admin = new User(3, 'Admin', 'admin@example.test', Roles::ADMIN);
        $marketing = new User(4, 'Marketing', 'marketing@example.test', Roles::MARKETING);
        $partner = new User(6, 'Partner', 'partner@example.test', Roles::PARTNER_FACILITY);
        $profile = $staff->createProfile($admin, $this->staffAttributes());
        $training = $staff->addTraining($admin, $profile['id'], 'Biosafety', '2026-06-20');
        $this->assert($training['status'] === 'expiring', 'Training expiry alert/status was not created.');
        $this->expectException(fn () => $staff->createProfile($marketing, $this->staffAttributes(['staff_code' => 'MKT-1'])));
        $this->expectException(fn () => $staff->createProfile($partner, $this->staffAttributes(['staff_code' => 'P-1'])));
    }

    public function testControlCenterShowsInventoryEquipmentStaffAndGovernanceCards(): void
    {
        $audit = new AuditLog();
        $super = new User(1, 'Super', 'super@example.test', Roles::SUPER_ADMIN);
        $inventory = new InventoryService($audit);
        $equipment = new EquipmentService($audit);
        $staff = new StaffService($audit);
        $financial = new FinancialGovernanceService($audit);
        $item = $inventory->createItem($super, $this->inventoryAttributes(['current_quantity' => 2, 'critical_level' => 3]));
        $equipment->register($super, $this->equipmentAttributes(['next_maintenance_date' => '2026-06-10']));
        $staff->createProfile($super, $this->staffAttributes());
        $financial->softDeleteBooking($super, ['id' => 5], 'Duplicate');
        $cards = (new ControlCenterService())->cards($inventory, $equipment, $staff, $audit);
        $this->assert(isset($cards['Inventory'], $cards['Equipment'], $cards['Staff'], $cards['Governance']), 'Control Center cards missing.');
        $this->assert($cards['Inventory']['critical stock items'] >= 1, 'Inventory card missing critical stock count.');
        $this->assert($cards['Governance']['pending booking deletions'] === 1, 'Governance card missing pending booking deletion count.');
    }

    private function assert(bool $condition, string $message): void
    {
        $this->assertions++;
        if (! $condition) {
            throw new RuntimeException($message);
        }
    }

    private function expectException(callable $callback): void
    {
        $this->assertions++;
        try {
            $callback();
        } catch (RuntimeException) {
            return;
        }

        throw new RuntimeException('Expected RuntimeException was not thrown.');
    }

    /** @return array{InventoryService,User} */
    private function inventoryFixture(): array
    {
        return [new InventoryService(new AuditLog()), new User(1, 'Super', 'super@example.test', Roles::SUPER_ADMIN)];
    }

    /** @param array<string,mixed> $overrides @return array<string,mixed> */
    private function inventoryAttributes(array $overrides = []): array
    {
        return array_merge([
            'name' => 'PCR Reagent A',
            'sku' => 'PCR-A',
            'category' => 'PCR reagents',
            'item_type' => 'reagent',
            'linked_service_category' => 'PCR',
            'linked_test_service' => 'PCR Test',
            'branch_code' => 'DHH',
            'unit' => 'kit',
            'current_quantity' => 20,
            'reorder_level' => 10,
            'critical_level' => 3,
            'expiry_date' => '2026-12-31',
            'batch_number' => 'B-001',
            'supplier' => 'Demy Supplier',
            'storage_condition' => '2-8C',
            'notes' => 'Readiness stock',
        ], $overrides);
    }

    /** @param array<string,mixed> $overrides @return array<string,mixed> */
    private function equipmentAttributes(array $overrides = []): array
    {
        return array_merge([
            'name' => 'PCR Analyzer',
            'asset_code' => 'EQ-PCR-001',
            'equipment_type' => 'Analyzer',
            'manufacturer' => 'DemyLab',
            'model' => 'DL-100',
            'serial_number' => 'SN-001',
            'branch_code' => 'DHH',
            'linked_service_category' => 'PCR',
            'linked_test_service' => 'PCR Test',
            'status' => 'active',
            'acquisition_date' => '2026-01-01',
            'warranty_expiry' => '2027-01-01',
            'last_maintenance_date' => '2026-03-01',
            'next_maintenance_date' => '2026-09-01',
            'last_calibration_date' => '2026-03-01',
            'next_calibration_date' => '2026-09-01',
            'notes' => 'Primary PCR capacity',
        ], $overrides);
    }

    /** @param array<string,mixed> $overrides @return array<string,mixed> */
    private function staffAttributes(array $overrides = []): array
    {
        return array_merge([
            'user_id' => null,
            'staff_code' => 'LAB-001',
            'full_name' => 'Demy Lab Manager',
            'email' => 'lab.manager@example.test',
            'phone' => '+2348000000000',
            'role' => 'lab_manager',
            'department' => 'Lab',
            'branch_code' => 'DHH',
            'employment_type' => 'full_time',
            'start_date' => '2026-06-01',
            'status' => 'active',
            'supervisor_id' => null,
            'emergency_contact' => 'Demy Contact',
            'notes' => 'No payroll or salary data stored.',
        ], $overrides);
    }
}
