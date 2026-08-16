<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receivable extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_RECEIVED = 'received';

    public const ACCOUNTS = [
        'Agents & Applicant Payment',
        'Applicant Expenses',
        'Commission Returns',
        'Monthly Collection',
        'Office Returns',
        'Other Collection',
        'Placement Fee',
        'Remittance Fee',
    ];

    public const DEBIT_ACCOUNTS = ['Receivable', 'Dollar Request'];

    public const TYPES = ['Partial', 'Full Payment'];

    public const MODES = ['Cash', 'Cheque', 'Fund Transfer', 'Credit', 'GCash'];

    protected $fillable = [
        'agency_id',
        'user_id',
        'agent_id',
        'applicant_id',
        'code',
        'date',
        'status',
        'ref_ar',
        'amount',
        'account',
        'debit_account',
        'type',
        'mode',
        'particular',
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

    public function histories(): HasMany
    {
        return $this->hasMany(ReceivableHistory::class)->latest();
    }

    /**
     * Generate the next 6-digit receipt code unique within this agency.
     */
    public static function nextCode(int $agencyId): string
    {
        $max = static::withoutGlobalScopes()
            ->where('agency_id', $agencyId)
            ->max('code');

        $next = $max ? ((int) $max) + 1 : 1;

        return str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
