<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Roles;

class FinanceActionPolicy
{
    private const SENSITIVE_ACTIONS = [
        'booking.delete', 'booking.restore', 'booking.critical_edit', 'booking.merge_duplicate',
        'booking.reverse_status', 'booking.cancel_after_linkage', 'payment.edit_amount',
        'payment.edit_method', 'payment.edit_reference', 'payment.void_capture',
        'payment.reverse_confirmation', 'payment.correct_overpayment', 'payment.correct_underpayment',
        'refund.approve', 'refund.reject', 'refund.mark_processed', 'invoice.edit_amount',
        'invoice.apply_discount', 'invoice.void', 'invoice.regenerate', 'invoice.correct_total',
        'invoice.reverse_status', 'receipt.void', 'receipt.regenerate', 'receipt.correct_metadata',
        'receipt.attach_replacement_proof',
    ];

    public function canExecute(User $user, string $action): bool
    {
        if (in_array($action, self::SENSITIVE_ACTIONS, true)) {
            return $user->role === Roles::SUPER_ADMIN;
        }

        return in_array($user->role, [Roles::SUPER_ADMIN, Roles::FINANCE], true);
    }

    public function canRequest(User $user, string $action): bool
    {
        return $user->role === Roles::FINANCE && in_array($action, self::SENSITIVE_ACTIONS, true);
    }
}
