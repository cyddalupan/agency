<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory, HasTenant;

    public const METHODS = ['cash', 'bank_transfer', 'check', 'gcash', 'online'];

    protected $fillable = [
        'agency_id',
        'account_id',
        'user_id',
        'amount',
        'date',
        'payee',
        'method',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
        'account_id' => 'integer',
        'user_id'    => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
