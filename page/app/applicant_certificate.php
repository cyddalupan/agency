<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class applicant_certificate extends Model {
	
    protected $table = 'applicant_certificate';
    protected $primaryKey = 'certificate_id';
    public $timestamps = false;

    public function applicant()
    {
        return $this->belongsTo('App\applicant', 'certificate_applicant', 'applicant_id');
    }

}