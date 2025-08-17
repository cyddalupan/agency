<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class applicant_passport extends Model {
	
    protected $table = 'applicant_passport';
    protected $primaryKey = 'passport_id';
    public $timestamps = false;

    public function applicant()
    {
        return $this->belongsTo('App\applicant', 'passport_applicant', 'applicant_id');
    }

}