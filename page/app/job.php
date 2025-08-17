<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class job extends Model {
	
    protected $table = 'job';
    protected $primaryKey = 'job_id';
    public $timestamps = false;

    public function applicants()
    {
        return $this->hasMany('App\applicants', 'applicant_job', 'job_id');
    }
}