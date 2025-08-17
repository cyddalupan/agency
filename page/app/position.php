<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class position extends Model {
	
    protected $table = 'position';
    protected $primaryKey = 'position_id';
    public $timestamps = false;

    public function applicant()
    {
        return $this->hasMany('App\applicant', 'applicant_preferred_position', 'position_id');
    }

    public function applicant_preferred_positions()
    {
        return $this->hasMany('App\applicant_preferred_positions', 'position_position', 'position_id');
    }

}