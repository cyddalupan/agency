<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class applicant_requirement extends Model {
	
    protected $table = 'applicant_requirement';
    protected $primaryKey = 'requirement_id';
    public $timestamps = false;

    public function applicants()
    {
        return $this->hasOne('App\applicants', 'applicant_id', 'requirement_applicant');
    }
}