<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class applicant_files extends Model {


    protected $table = 'applicant_files';
    protected $primaryKey = 'file_id';
    public $timestamps = false;

    public function applicant()
    {
        return $this->belongsTo('App\applicant', 'file_applicant', 'applicant_id');
    }
  
    public function user()
    {
        return $this->belongsTo('App\user_system', 'file_createdby', 'user_id');
    }
    
    public function scopeFilesOf($query, $applicant_id)
    {
        return $query->where('file_applicant', $applicant_id);
    }

}
