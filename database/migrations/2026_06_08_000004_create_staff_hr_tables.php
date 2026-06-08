<?php

return [
    'staff_profiles' => [
        'user_id', 'staff_code', 'full_name', 'email', 'phone', 'role', 'department', 'branch_code',
        'employment_type', 'start_date', 'status', 'supervisor_id', 'emergency_contact', 'notes',
    ],
    'staff_documents' => [
        'staff_profile_id', 'document_type', 'title', 'file_path', 'expiry_date', 'status',
    ],
    'staff_trainings' => [
        'staff_profile_id', 'training_title', 'training_type', 'provider', 'completed_at', 'expires_at', 'certificate_path', 'status',
    ],
    'staff_attendance_logs' => [
        'staff_profile_id', 'branch_code', 'clock_in', 'clock_out', 'notes',
    ],
    'staff_performance_notes' => [
        'staff_profile_id', 'reviewer_id', 'note_type', 'title', 'comments', 'created_at',
    ],
];
