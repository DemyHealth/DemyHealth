<?php

return new class
{
    public function up(): string
    {
        return "ALTER TABLE users ADD COLUMN role VARCHAR(50) NULL DEFAULT 'admin' CHECK (role IN ('super_admin','admin','finance','lab_manager','lab_staff','dispatch','marketing','partner_facility'))";
    }

    public function down(): string
    {
        return 'ALTER TABLE users DROP COLUMN role';
    }
};
