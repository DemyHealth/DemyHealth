<?php

return [
    'equipment' => [
        'name', 'asset_code', 'equipment_type', 'manufacturer', 'model', 'serial_number', 'branch_code',
        'linked_service_category', 'linked_test_service', 'status', 'acquisition_date', 'warranty_expiry',
        'last_maintenance_date', 'next_maintenance_date', 'last_calibration_date', 'next_calibration_date', 'notes',
    ],
    'equipment_events' => [
        'equipment_id', 'event_type', 'event_date', 'performed_by', 'vendor', 'notes', 'attachment_path', 'next_due_date',
    ],
    'equipment_alerts' => [
        'equipment_id', 'alert_type', 'severity', 'message', 'status', 'resolved_by', 'resolved_at',
    ],
];
