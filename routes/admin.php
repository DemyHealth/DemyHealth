<?php

use App\Http\Middleware\SuperAdminOnly;

return [
    'GET /admin/governance' => ['name' => 'admin.governance.index', 'roles' => ['super_admin', 'admin', 'finance']],
    'GET /admin/inventory' => ['name' => 'admin.inventory.index', 'roles' => ['super_admin', 'admin', 'lab_manager', 'lab_staff', 'finance']],
    'GET /admin/equipment' => ['name' => 'admin.equipment.index', 'roles' => ['super_admin', 'admin', 'lab_manager', 'lab_staff', 'finance']],
    'GET /admin/staff' => ['name' => 'admin.staff.index', 'roles' => ['super_admin', 'admin', 'lab_manager', 'finance']],
    'POST /admin/financial-corrections/{payment}' => ['name' => 'admin.financial-corrections.update', 'middleware' => [SuperAdminOnly::class]],
];
