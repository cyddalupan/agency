<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseRequestStatusHistory extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'expense_request_histories';

    protected $fillable = [
        'agency_id',
        'expense_request_id',
        'user_id',
        'from_status',
        'to_status',
        'note',
    ];

    public function expenseRequest(): BelongsTo
    {
        return $this->belongsTo(ExpenseRequest::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
