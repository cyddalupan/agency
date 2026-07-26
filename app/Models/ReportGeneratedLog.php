<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportGeneratedLog extends Model
{
    protected $fillable = [
        'agency_id',
        'user_id',
        'report_template_id',
        'format',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportTemplate()
    {
        return $this->belongsTo(ReportTemplate::class);
    }
}
