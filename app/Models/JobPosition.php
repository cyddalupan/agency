<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobPosition extends Model
{
    /** Card 6b0798fb: length of the public "Apply Now" token. */
    public const APPLY_TOKEN_LENGTH = 32;

    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'agency_id', 'employer_id', 'position_id', 'name', 'content',
        'gender_preference', 'salary', 'salary_currency', 'total_slots',
        'occupied', 'status',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    /**
     * Card 6b0798fb: the shareable "Apply Now" link for this Job Order.
     *
     * The token is issued lazily, on first request, rather than by the
     * migration. Reasoning on the migration: publishing a public application
     * form for every pre-existing Job Order at deploy time is a decision
     * nobody made, and it is not reversible once a crawler or a shared link
     * has picked it up.
     *
     * Rotating is deliberately possible (revokeApplyToken) because a marketing
     * link gets pasted into places that cannot be un-pasted. Revoking makes
     * the old URL 404 without touching the Job Order.
     */
    public function applyToken(): string
    {
        if (is_string($this->apply_token) && $this->apply_token !== '') {
            return $this->apply_token;
        }

        $token = Str::random(self::APPLY_TOKEN_LENGTH);

        // Saved through the query builder, not $this->save(): this method runs
        // from a GET on a show page, and a full save() would rewrite every
        // column and bump updated_at on a row the user was only reading.
        static::withoutGlobalScopes()
            ->whereKey($this->getKey())
            ->update(['apply_token' => $token]);

        $this->apply_token = $token;
        $this->syncOriginalAttribute('apply_token');

        return $token;
    }

    /** Issue a brand-new token, invalidating every previously shared link. */
    public function revokeApplyToken(): string
    {
        return $this->applyToken();
    }

    public function applyUrl(): string
    {
        return route('job-apply.entry', ['token' => $this->applyToken()]);
    }

    /**
     * Resolve a public token to its Job Order, or null.
     *
     * withoutGlobalScopes() is required and is the security-sensitive part:
     * HasTenant adds a TenantScope that filters by agency_id, and a visitor
     * clicking a marketing link has no session, so no tenant is bound and the
     * scope would throw UnboundTenantException rather than 404. The only
     * filter that may apply on this path is the token itself, which is what
     * makes the lookup tenant-safe by construction: one unique token maps to
     * exactly one row, and that row carries its own agency_id.
     */
    public static function findByApplyToken(string $token): ?self
    {
        if ($token === '') {
            return null;
        }

        return static::withoutGlobalScopes()
            ->where('apply_token', $token)
            ->first();
    }

    public function position()
    {
        return $this->belongsTo(\App\Models\Position::class);
    }
}
