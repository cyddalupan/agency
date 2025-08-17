<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class recruitment_agent extends Model {
	
    protected $table = 'recruitment_agent';
    protected $primaryKey = 'agent_id';
    public $timestamps = false;

    //relations
    public function applicant()
    {
        return $this->hasMany('App\applicant', 'applicant_source', 'agent_id');
    }
}