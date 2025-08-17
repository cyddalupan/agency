<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class CaseManagementApi extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function hitApplicants()
	{
		//get option from json
		$option_json = file_get_contents(url()."/../assets/json/option.json"); 
		$option_json = utf8_encode($option_json); 
		$option_array = json_decode($option_json);

		//get the least updated applicant
		$applicants = DB::table('applicant')->where('applicant_status', '!=' , 9)->orderBy('hit_date', 'asc')->take(1)->get();
		/**
		 * Save New Hit Date
		 */
		DB::table('applicant')
            ->where('applicant_id', $applicants[0]->applicant_id)
            ->update([
            	'hit_date' => date('Y-m-d H:i:s')
            ]);
        //return name
        echo $applicants[0]->applicant_first.' '.$applicants[0]->applicant_last;

		$pass_array = array();
		$pass_array['fname'] = str_replace('/','',$applicants[0]->applicant_first); 
		$pass_array['lname'] = str_replace('/','',$applicants[0]->applicant_last);
		$pass_array['mname'] = str_replace('/','',$applicants[0]->applicant_middle);
		$pass_array['birthdate'] = $applicants[0]->applicant_birthdate;
		$pass_array['gender'] = $applicants[0]->applicant_gender;
		$pass_array['contacts'] = $applicants[0]->applicant_contacts;

		//get passport number
		$passport = DB::table('applicant_passport')->where('passport_applicant', $applicants[0]->applicant_id)->first();		
		if(isset($passport->passport_number))
			$pass_array['passport_number'] = str_replace('/','',$passport->passport_number);
		else
			$pass_array['passport_number'] = 0;

		//get authentication key
		$pass_array['auth'] = md5(date('YmdH')."cydApi");

		/**
		 * convert to json 
		 * and convert to array friendly text
		 * before sending to api
		 */
		$pass_json = json_encode($pass_array);
		$pass_url = urlencode($pass_json);

		//send to API
		$apiReturn = file_get_contents($option_array->api_url.'api/hit-applicants/'.$pass_url);
		$apiReturn = utf8_encode($apiReturn); 
		$apiReturn = json_decode($apiReturn);

		/**
		 * And Update User Status if Hit or Cleared
		 */
		DB::table('applicant')
            ->where('applicant_id', $applicants[0]->applicant_id)
            ->update([
            	'hit_hearing_date' => $apiReturn->hearing_date,
            	'hit_id' => $apiReturn->hit_id,
            	'hit_status' => $apiReturn->hit_status
            ]);
		//give proper response
        echo ' Has Been '.$apiReturn->hit_status;
	}

	function getHitCount(){
		echo DB::table('applicant')->where('hit_status', 'hit')->count();
	}

	function checkHearingDate(){
		$query = DB::table('applicant')
			->where('hit_status', 'hit')
			->orderBy('hit_hearing_date', 'asc')
			->take(1)
			->get();

		echo json_encode($query);
	}
	
}
