<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class country extends Model {
	
    protected $table = 'country';
    protected $primaryKey = 'country_id';
    public $timestamps = false;

    public function applicant()
    {
        return $this->belongsTo('App\applicant', 'country_id', 'applicant_preferred_country');
    }
}