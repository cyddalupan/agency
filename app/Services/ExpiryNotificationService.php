<?php

namespace App\Services;

use App\Models\ApplicantMedical;
use App\Models\ApplicantVisa;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

/**
 * Mjolnir "LANDAS: POP-UP Expire notification" (2026-10-08).
 *
 * Collects documents that are about to expire so the dashboard can raise a
 * pop-up alert on login:
 *
 *   - Medical : expiring within 2 weeks  (Name, Clinic, Issue Date, Expire Date)
 *   - Visa    : expiring within 1 month   (Name, Branch, FRA, Visa No., Expire Date)
 *
 * Scoping matches the rest of the dashboard: TenantScope limits results to the
 * current agency, and branch-locked accounts only see their own branch.
 */
class ExpiryNotificationService
{
    /** Medical documents expiring within this many days. */
    public const MEDICAL_WINDOW_DAYS = 14;

    /** Visa documents expiring within this many days. */
    public const VISA_WINDOW_DAYS = 30;

    /**
     * Medicals expiring within the alert window (inclusive of today).
     *
     * @return Collection<int, ApplicantMedical>
     */
    public function medicals(?Authenticatable $user = null): Collection
    {
        return ApplicantMedical::query()
            ->with(['applicant.branch', 'applicant.employer'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->addDays(self::MEDICAL_WINDOW_DAYS)->toDateString())
            ->when($this->branchLocked($user), fn ($q) => $this->scopeToBranch($q, $user))
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Visas expiring within the alert window (inclusive of today).
     *
     * @return Collection<int, ApplicantVisa>
     */
    public function visas(?Authenticatable $user = null): Collection
    {
        return ApplicantVisa::query()
            ->with(['applicant.branch', 'applicant.employer'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->whereDate('expiry_date', '<=', now()->addDays(self::VISA_WINDOW_DAYS)->toDateString())
            ->when($this->branchLocked($user), fn ($q) => $this->scopeToBranch($q, $user))
            ->orderBy('expiry_date')
            ->get();
    }

    private function branchLocked(?Authenticatable $user): bool
    {
        $user = $user ?? auth()->user();

        return $user && method_exists($user, 'isBranchLocked') && $user->isBranchLocked();
    }

    private function scopeToBranch($query, ?Authenticatable $user)
    {
        $user = $user ?? auth()->user();

        return $query->whereHas('applicant', fn ($a) => $a->where('branch_id', $user->branch_id));
    }
}
