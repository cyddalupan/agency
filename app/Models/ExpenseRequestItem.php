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
        'account_id',
        'particular',
        'file_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

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
