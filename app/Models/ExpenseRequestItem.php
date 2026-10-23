<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_request_id',
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
