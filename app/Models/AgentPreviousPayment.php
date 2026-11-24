<?php

namespace App\Models;

use App\Models\Traits\AuditsChanges;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Gulf only (card 6b056b38): payments made to an agent *before* the agency
 * migrated in. Append-only history — rows are inserted by the bulk upload and
 * deleted by an admin with a reason; nothing here is ever matched to an
 * expense request or rewritten by a later upload.
 *
 * Deliberately NOT a StartingBalance: that table is one row per agent (UNIQUE
 * agency_id, agent_id) and belongs to the Starting Balance tab.
 */
class AgentPreviousPayment extends Model
{
    use HasFactory, HasTenant, SoftDeletes, AuditsChanges;

    protected $fillable = [
        'agency_id',
        'user_id',
        'agent_id',
        'applicant_id',
        'date',
        'currency',
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
     * Deletions are audited (who + why), not silent.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
