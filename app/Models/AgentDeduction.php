<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentDeduction extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    /** Account selection per spec: Paid | Deduction */
    public const ACCOUNT_PAID      = 'Paid';
    public const ACCOUNT_DEDUCTION = 'Deduction';

    public const ACCOUNTS = [self::ACCOUNT_PAID, self::ACCOUNT_DEDUCTION];

    protected $fillable = [
        'agency_id',
        'user_id',
        'agent_id',
        'applicant_id',
        'expense_request_item_id',
        'date',
        'account',
        'amount',
        'particular',
        'deleted_by',
        'delete_reason',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /**
     * Cyd 2026-11-01: deletions are audited (who + why), not silent.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
