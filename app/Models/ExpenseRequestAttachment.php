<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One uploaded file on an expense request (Cyd 2026-11-09, "Upload and Edit").
 * A request may hold up to 5 of these; more can be added on each edit while the
 * request is Pending.
 */
class ExpenseRequestAttachment extends Model
{
    protected $table = 'expense_request_attachments';

    protected $fillable = [
        'expense_request_id',
        'expense_request_item_id',
        'path',
        'original_name',
        'mime',
        'size',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function expenseRequest(): BelongsTo
    {
        return $this->belongsTo(ExpenseRequest::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
