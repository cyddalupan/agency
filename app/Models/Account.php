<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'agency_id',
        'parent_id',
        'name',
        'type',
        'charge_type', // office|agent — for Expenses & Payments CoA gating
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'parent_id' => 'integer',
    ];

    /**
     * Whether this account is a Main account (no parent).
     */
    public function isMain(): bool
    {
        return $this->parent_id === null;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Main accounts only (top of the tree).
     */
    public function scopeMains($query)
    {
        return $query->whereNull('parent_id');
    }
}
