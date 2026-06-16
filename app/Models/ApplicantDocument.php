<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class ApplicantDocument extends Model
{
    use HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'document_type', 'file_name', 'file_path', 'mime_type', 'file_size', 'notes'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
