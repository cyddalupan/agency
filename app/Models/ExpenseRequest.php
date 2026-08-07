<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_RECEIVED = 'received';

    protected $fillable = [
        'agency_id',
        'user_id',
        'reference_no',
        'date',
        'status',
        'branch_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseRequestItem::class)->orderBy('id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ExpenseRequestStatusHistory::class)->latest();
    }
}
