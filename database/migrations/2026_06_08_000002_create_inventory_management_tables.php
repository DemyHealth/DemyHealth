<?php

return [
    'inventory_items' => [
        'name', 'sku', 'category', 'item_type', 'linked_service_category', 'linked_test_service',
        'branch_code', 'unit', 'current_quantity', 'reorder_level', 'critical_level', 'expiry_date',
        'batch_number', 'supplier', 'storage_condition', 'status', 'notes',
    ],
    'inventory_movements' => [
        'inventory_item_id', 'movement_type', 'quantity', 'branch_code', 'reason', 'performed_by',
        'approved_by', 'reference_type', 'reference_id', 'created_at',
    ],
    'inventory_alerts' => [
        'inventory_item_id', 'alert_type', 'branch_code', 'service_category', 'message', 'severity',
        'status', 'resolved_by', 'resolved_at',
    ],
];
