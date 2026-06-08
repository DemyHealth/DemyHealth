<?php

namespace App\Services;

use App\Models\User;
use App\Policies\FinanceActionPolicy;
use RuntimeException;

final class FinancialGovernanceService
{
    public function __construct(private AuditLog $auditLog, private FinanceActionPolicy $policy = new FinanceActionPolicy()) {}

    /** @param array<string,mixed> $payment */
    public function correctPaymentAmount(User $actor, array $payment, float $newAmount, string $reason): array
    {
        $this->guard($actor, 'payment.edit_amount');
        $original = $payment;
        $payment['amount'] = $newAmount;
        $payment['correction_reason'] = $reason;
        $payment['original_values'] = $original;
        $this->auditLog->record('financial.payment_amount_corrected', $actor, [
            'payment_id' => $payment['id'] ?? null,
            'original_amount' => $original['amount'] ?? null,
            'new_amount' => $newAmount,
            'reason' => $reason,
        ]);

        return $payment;
    }

    /** @param array<string,mixed> $booking */
    public function softDeleteBooking(User $actor, array $booking, string $reason): array
    {
        $this->guard($actor, 'booking.delete');
        $booking['deleted_at'] = date(DATE_ATOM);
        $booking['deletion_reason'] = $reason;
        $this->auditLog->record('booking.soft_deleted', $actor, ['booking_id' => $booking['id'] ?? null, 'reason' => $reason]);

        return $booking;
    }

    /** @param array<string,mixed> $booking */
    public function restoreBooking(User $actor, array $booking, string $reason): array
    {
        $this->guard($actor, 'booking.restore');
        $booking['deleted_at'] = null;
        $booking['restore_reason'] = $reason;
        $this->auditLog->record('booking.restored', $actor, ['booking_id' => $booking['id'] ?? null, 'reason' => $reason]);

        return $booking;
    }

    private function guard(User $actor, string $action): void
    {
        if (! $this->policy->canExecute($actor, $action)) {
            throw new RuntimeException('Sensitive financial edit denied; super_admin approval is required.');
        }
    }
}
