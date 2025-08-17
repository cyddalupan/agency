<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class applicant_log extends Model {
	
    protected $table = 'applicant_log';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    public function applicant()
    {
        return $this->belongsTo('App\applicant', 'log_applicant', 'applicant_id');
    }

    public function user_system()
    {
        return $this->belongsTo('App\user_system', 'log_createdby', 'user_id');
    }

    public function Status()
    {
        return $this->belongsTo('App\Status', 'log_status', 'number');
    }

    public function employer()
    {
        return $this->belongsTo('App\employer', 'log_employer', 'employer_id');
    }

    public function scopeSelected($query)
    {
        return $query->where('log_status',4);
    }

    public function scopeDeployed($query)
    {
        return $query->where('log_status',9);
    }

    public function scopeLineup($query)
    {
        return $query->where('log_status',5);
    }
}