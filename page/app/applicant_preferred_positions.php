<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class applicant_preferred_positions extends Model {
	
    protected $table = 'applicant_preferred_positions';
    protected $primaryKey = 'position_id';
    public $timestamps = false;

    public function applicant()
    {
        return $this->belongsTo('App\Applicant', 'position_applicant', 'applicant_id');
    }

    public function position()
    {
        return $this->belongsTo('App\position', 'position_position', 'position_id');
    }

}