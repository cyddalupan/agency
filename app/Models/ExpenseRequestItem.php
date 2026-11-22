<?php

namespace App\Models;

use App\Models\Scopes\ExpenseRequestItemTenantScope;
use App\Models\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseRequestItem extends Model
{
    use HasFactory, AuditsChanges;

    /**
     * Card 6b008b8e7c2a220f1cd6d2e1: the inheritance below is ENFORCED, not
     * merely intended. The table still has no agency_id; the scope filters
     * through the parent request and takes its tenant from
     * TenantScope::tenantFilter(), so items cannot be read across agencies by
     * a query that forgot the parent. Raw DB::table()/join readers are not
     * covered by this and must constrain themselves — see the scope class.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ExpenseRequestItemTenantScope());
    }

    /**
     * QMS-02: expense_request_items carries no agency_id of its own — it
     * inherits the tenant from its parent request.
     */
    protected function resolveAuditAgencyId(): ?int
    {
        $own = parent::resolveAuditAgencyId();

        if ($own !== null) {
            return $own;
        }

        return $this->expenseRequest?->agency_id !== null
            ? (int) $this->expenseRequest->agency_id
            : null;
    }

    protected $fillable = [
        'expense_request_id',
        'branch_id',   // per-line branch (CYD 2026-11-04, main office users)
        'charge',        // office|agent
        'agent_id',
        'applicant_id',
        'country_id',
        'currency',      // PHP|USD
        'amount',
        'payment',       // already paid to them; net = amount - payment
        'status',        // per-transaction status (Cyd 2026-10-23)
        'account_id',
        'particular',
        'file_path',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'payment' => 'decimal:2',
    ];

    /**
     * Net amount (Cyd 2026-11-04, card 131): what is actually released to the
     * agent once the payment already handed over is deducted. Released
     * Commission must display this, not the gross `amount`.
     */
    public function netAmount(): float
    {
        return (float) $this->amount - (float) ($this->payment ?? 0);
    }

    /**
     * Per-transaction status (Cyd 2026-10-23). A request can hold several
     * transactions and they are approved one at a time, so the status lives on
     * the line item; the request keeps a rolled-up status for reports.
     */
    public function statusLabel(): string
    {
        return ExpenseRequest::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function statusBadge(): string
    {
        return ExpenseRequest::STATUS_BADGES[$this->status] ?? 'badge-ghost';
    }

    public function expenseRequest(): BelongsTo
    {
        return $this->belongsTo(ExpenseRequest::class);
    }

    /** Per-line branch (falls back to the parent request's branch when null). */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
