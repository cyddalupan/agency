<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Card 6b008b8d3ca32bfcb58ce970: agents are tenant-owned (437 rows across
 * agencies 1, 7, 8 and 27; zero null agency_id).
 *
 * Why this model is the special case on the card.
 *
 * Agent extends Authenticatable and is the `agent` guard's provider model, so
 * EloquentUserProvider queries `agents` during Auth::guard('agent')->attempt()
 * and while restoring a session. Measure it, do not guess it: on the control
 * plane (main host, /agent/login) IdentifyAgency binds no tenant and the scope
 * would fail closed with UnboundTenantException — no agent could log in, the
 * exact shape of 392b05c3 for the applicant portal.
 *
 * The measurement also shows the current app DOES bind a tenant before that
 * lookup on a tenant host: the login page runs on a real agency host, so the
 * auth query is already scoped to the host's agency. That is what makes a
 * blanket `use HasTenant` safe to ship here, and it is also the hole this
 * card's test exposes: an agent belonging to agency B could log in on agency
 * A's host, because the lookup was filtered by the HOST, never by the agent's
 * own agency.
 *
 * So the guard is kept scoped, and AgentAuthController refuses an agent whose
 * agency_id is not the host's agency. No escape from the scope is registered
 * for authentication — see AgentGuardTenantBindingTest.
 */
class Agent extends Authenticatable
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'agency_id',
        'branch_id',
        'name',
        'email',
        'contact',
        'password',
        'commission_rate',
        'status',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** The user who created this agent (Cyd / accounting). */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }
}
