<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model {
	
    protected $table = 'statuses';

    
    public function recruitment_agent()
    {
        return $this->belongsTo('App\applicant', 'number', 'applicant_status');
    }
}
