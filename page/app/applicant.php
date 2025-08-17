<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class applicant extends Model {
	
    protected $table = 'applicant';
    protected $primaryKey = 'applicant_id';
    public $timestamps = false;

    //relations
    public function status()
    {
        return $this->hasOne('App\Status', 'number', 'applicant_status');
    }

    public function position()
    {
        return $this->hasOne('App\position', 'position_id', 'applicant_preferred_position');
    }

    public function log()
    {
        return $this->hasMany('App\applicant_log', 'log_applicant', 'applicant_id');
    }

    public function recruitment_agent()
    {
        return $this->belongsTo('App\recruitment_agent', 'applicant_source', 'agent_id');
    }

    public function employer()
    {
        return $this->belongsTo('App\employer', 'applicant_employer', 'employer_id');
    }

    public function job()
    {
        return $this->belongsTo('App\job', 'applicant_job', 'job_id');
    }

    public function applicant_requirement()
    {
        return $this->belongsTo('App\applicant_requirement', 'applicant_id', 'requirement_applicant');
    }
    
    public function applicant_experiences()
    {
        return $this->hasMany('App\applicant_experiences', 'experience_applicant', 'applicant_id');
    }

    public function applicant_passport()
    {
        return $this->hasOne('App\applicant_passport', 'passport_applicant', 'applicant_id');
    }

    public function applicant_preferred_positions()
    {
        return $this->hasMany('App\applicant_preferred_positions', 'position_applicant', 'applicant_id');
    }
    
    public function applicant_certificate()
    {
        return $this->hasOne('App\applicant_certificate', 'certificate_applicant', 'applicant_id');
    }

    public function user_updated()
    {
        return $this->hasOne('App\user_system', 'user_id', 'applicant_updatedby');
    }

    public function country()
    {
        return $this->hasOne('App\country', 'country_id', 'applicant_preferred_country');
    }
    
    public function multiple_lineup()
    {
        return $this->hasMany('App\multiple_lineup', 'applicant_id', 'applicant_id');
    }

    //scopes
    public function scopeSelected($query)
    {
        return $query->where('applicant_status',4);
    }

    public function scopeDeployed($query)
    {
        return $query->where('applicant_status',9);
    }
    
    public function scopeLineup($query)
    {
        return $query->where('applicant_status',5);
    }
}