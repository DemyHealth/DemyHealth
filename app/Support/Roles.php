<?php

namespace App\Support;

final class Roles
{
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN = 'admin';
    public const FINANCE = 'finance';
    public const LAB_MANAGER = 'lab_manager';
    public const LAB_STAFF = 'lab_staff';
    public const DISPATCH = 'dispatch';
    public const MARKETING = 'marketing';
    public const PARTNER_FACILITY = 'partner_facility';

    public const ALL = [
        self::SUPER_ADMIN,
        self::ADMIN,
        self::FINANCE,
        self::LAB_MANAGER,
        self::LAB_STAFF,
        self::DISPATCH,
        self::MARKETING,
        self::PARTNER_FACILITY,
    ];

    public const OFFICIAL_BRANCH_CODES = ['DHH', 'DHA', 'DHM', 'DHI', 'ABA'];

    public static function isValid(string $role): bool
    {
        return in_array($role, self::ALL, true);
    }

    public static function isOfficialBranch(string $branchCode): bool
    {
        return in_array($branchCode, self::OFFICIAL_BRANCH_CODES, true);
    }
}
