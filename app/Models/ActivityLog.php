<?php

namespace App\Models;

use App\Models\Traits\ImmutableAuditRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Application activity log (login, role changes, exports, applicant status
 * history).
 *
 * QMS-05: append-only. Rows are created freely but can no longer be updated or
 * deleted through the model; see ImmutableAuditRecord for why and for the
 * deliberate DB-level escape hatch.
 */
class ActivityLog extends Model
{
    use HasFactory;
    use ImmutableAuditRecord;

    protected $fillable = [
        'agency_id',
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
