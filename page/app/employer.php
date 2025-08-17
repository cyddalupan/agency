<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class employer extends Model {
	
    protected $table = 'employer';
    protected $primaryKey = 'employer_id';
    public $timestamps = false;

    public function applicants()
    {
        return $this->hasMany('App\applicants', 'applicant_employer', 'employer_id');
    }
}