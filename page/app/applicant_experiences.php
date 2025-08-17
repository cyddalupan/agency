<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class applicant_experiences extends Model {
	
    protected $table = 'applicant_experiences';
    protected $primaryKey = 'experience_id';
    public $timestamps = false;

    
    public function applicant()
    {
        return $this->belongsTo('App\applicant', 'experience_applicant', 'applicant_id');
    }
}