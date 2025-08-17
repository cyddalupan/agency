<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class multiple_lineup extends Model {

    protected $table = 'multiple_lineups';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function employer()
    {
        return $this->hasMany('App\employer', 'employer_id', 'applicant_employer');
    }
}
