<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusCode extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $fillable = ['code', 'label', 'label_saudi', 'description', 'color', 'sort_order'];

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'status_code', 'code');
    }
}
