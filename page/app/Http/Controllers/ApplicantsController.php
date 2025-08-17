<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;

use App\applicant;
use App\applicant_files;
use App\multiple_lineup;
use App\employer;
use App\country;
use App\position;
use App\applicant_log;
use Cache;
use Session;

class ApplicantsController extends Controller {
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$id = Request::input('fileid');
		applicant_files::destroy($id);
		return 'file deleted';
	}

	public function applicant_files()
	{
		$applicant_id = Request::input('applicant_id');
		$files = applicant_files::filesOf($applicant_id)->get()->toJson();
		echo $files;
	}

	public function multiple_lineup(){
		$applicant_ids = Request::input('applicant_ids');
		$employer = Request::input('employer');

		foreach ($applicant_ids as $applicant_id) {	
			$multiple_lineup = new multiple_lineup;
			$multiple_lineup->applicant_id = $applicant_id;
			$multiple_lineup->applicant_employer = $employer;
			$multiple_lineup->save();
		}
		
		echo 'multiple lineup inserted';
	}

	public function get_currency(){
		$applicant_id = Request::input('applicant_id');
		$applicant = applicant::find($applicant_id);
		return $applicant->currency;
	}

	public function all_applicants(){

		//get all inputs
		$inputs = Request::all();

		$page = '';
		if(isset($_GET["page"]))
		$page = $_GET["page"];
		
		//static search
		$staticsearch = implode("|",$inputs);
		return Cache::remember('jsonapplicants'.$page.$staticsearch, "4", function()use($inputs)
		{
			//get model
			$applicants = applicant::query();

			//if search keyword exist
			if($inputs['keyword'] != ''){
				$keyword = $inputs['keyword'];
				$applicants = $applicants->where(function($query)use($keyword){
	            	$query->orWhere('applicant_first', 'LIKE', '%'.$keyword.'%')
					->orWhere('applicant_middle', 'LIKE', '%'.$keyword.'%')
					->orWhere('applicant_last', 'LIKE', '%'.$keyword.'%')
					->orWhere('applicant_id', 'LIKE', '%'.$keyword.'%')
					->orWhere('applicant_remarks', 'LIKE', '%'.$keyword.'%')
					->orWhere('applicantNumber', 'LIKE', '%'.$keyword.'%')
					->orWhereHas('applicant_passport', function($q)use($keyword){
					    $q->where('passport_number', 'LIKE', '%'.$keyword.'%');
					})
					->orWhereHas('position', function($q)use($keyword){
					    $q->where('position_name', 'LIKE', '%'.$keyword.'%');
					})
					->orWhereHas('applicant_preferred_positions.position', function($q)use($keyword){
					    $q->where('position_name', 'LIKE', '%'.$keyword.'%');
					})
					->orWhereHas('applicant_requirement', function($q)use($keyword){
					    $q->where('requirement_oec_number', 'LIKE', '%'.$keyword.'%');
					})
					->orWhereHas('applicant_requirement', function($q)use($keyword){
					    $q->where('ticket_no', 'LIKE', '%'.$keyword.'%');
					})
					->orWhereHas('recruitment_agent', function($q)use($keyword){
					    $q->where('agent_first', 'LIKE', '%'.$keyword.'%');
					})
					
						->orWhereHas('recruitment_agent', function($q)use($keyword){
					    $q->where('agent_last', 'LIKE', '%'.$keyword.'%');
					})
					
					
					->orWhereHas('applicant_certificate', function($q)use($keyword){
					    $q->where('insurance_no', 'LIKE', '%'.$keyword.'%');
					});
					
	            });
			}

			//applicant_position_type exist
			if($inputs['applicant_position_type'] != 'null'){
				$applicants = $applicants->where('applicant_position_type', $inputs['applicant_position_type']);
			}
			

			if($inputs['isTrainingAdmin'] != 0){
				$applicants = $applicants->where('training_branches_id', '!=', 0);
			}elseif($inputs['branchID'] != 0){
				$applicants = $applicants->where('training_branches_id', $inputs['branchID']);
			}

			//if search country exist
			if($inputs['search_country'] != 0){
				$applicants = $applicants->where('applicant_preferred_country', $inputs['search_country']);
			}

			if($inputs['status'] != 'all'){

				$url_status = 999;

				if($inputs['status'] == 'Available'){
					$url_status = 0;
				}
				elseif ($inputs['status'] == 'Cancelled') {
					$url_status = 1;
				}
				elseif ($inputs['status'] == 'Reserved') {
					$url_status = 2;
				}
				elseif ($inputs['status'] == 'Selected') {
					$url_status = 4;
				}
				elseif ($inputs['status'] == 'Line Up') {
					$url_status = 5;
				}
				elseif ($inputs['status'] == 'For Deployment') {
					$url_status = 8;
				}
				elseif ($inputs['status'] == 'Deployed') {
					$url_status = 9;
				}
				elseif ($inputs['status'] == 'For Review') {
					$url_status = 10;
				}
				elseif ($inputs['status'] == 'For Review2') {
					$url_status = 10;

					$applicants = $applicants->where('training_branches_id', '!=' ,0 )
					->whereHas('user_updated', function($q)
					{
					    $q->where('user_type', 12);
					});
				}

				if($url_status != 999){
					$applicants = $applicants->where('applicant_status',$url_status );
				}
			}
			
			//if applicant_position_type is on
			if($inputs['skill'] != 'all'){
				$applicants = $applicants->where('applicant_position_type', $inputs['skill']);
			}

			//if search position exist
			if($inputs['search_position'] != 0){
				$applicants = $applicants->where(function($query)use($inputs){
					$query->where('applicant_preferred_position', $inputs['search_position'])
					->orWhereHas('applicant_preferred_positions', function($q)use($inputs){
					    $q->where('position_position', $inputs['search_position']);
					});
	            });
			}

			//if search employer exist
			if($inputs['search_employer'] != 0){
				$applicants = $applicants->where('applicant_employer', $inputs['search_employer']);
			}
			
			//if search statis exist
			if($inputs['search_status'] != 111){
				$applicants = $applicants->where('applicant_status', $inputs['search_status']);
			}

			//if search gender exist
			if($inputs['search_gender'] != 'any'){
				$applicants = $applicants->where('applicant_gender', $inputs['search_gender']);
			}

			//if search date_from exist
			if($inputs['search_date_from'] != ''){
				$applicants = $applicants->where('applicant_created', '>=' ,$inputs['search_date_from']);
			}
			//if search date_to exist
			if($inputs['search_date_to'] != ''){
				$applicants = $applicants->where('applicant_created', '<=' ,$inputs['search_date_to']);
			}

			//if search date_from exist
			if($inputs['search_age_from'] != ''){
				$applicants = $applicants->where('applicant_age', '>=' ,$inputs['search_age_from']);
			}
			//if search date_to exist
			if($inputs['search_age_to'] != ''){
				$applicants = $applicants->where('applicant_age', '<=' ,$inputs['search_age_to']);
			}

			//if search date_from exist
			if($inputs['search_salary_from'] != ''){
				$applicants = $applicants->whereHas('applicant_requirement', function($q)use($inputs){
				    $q->where('requirement_offer_salary',  '>=' ,$inputs['search_salary_from']);
				});
			}
			//if search date_to exist
			if($inputs['search_salary_to'] != ''){
				$applicants = $applicants->whereHas('applicant_requirement', function($q)use($inputs){
				    $q->where('requirement_offer_salary',  '<=' ,$inputs['search_salary_to']);
				});
			}

			//get all relation
			$applicants = $applicants
				->with('employer')
				->with('multiple_lineup.employer')
				->with('job')
				//->with('employer')
				->with('log')
				->with('position')
				->with('country')
				->with('user_updated')
				->with('recruitment_agent')
				->with('country')
				->with('applicant_requirement')
				->with('applicant_experiences')
				->with('applicant_passport')
				->with('recruitment_agent')
				->with('applicant_preferred_positions.position')
				->with('applicant_certificate')
				/*applicant_created*/
				->orderBy('applicant_created','desc')
				->paginate(20);

		
			return $applicants->toJson();
		
		});
	}
	
	public function quick_search($keyword){
		$page = '';
		if(isset($_GET["page"]))
			$page = $_GET["page"];
	
		$applicants = applicant::where('applicantNumber', 'like', '%'.$keyword.'%')
			->orWhere('applicant_first', 'like', '%'.$keyword.'%')
			->orWhere('applicant_last', 'like', '%'.$keyword.'%')
			->orWhere('sub_employer', 'like', '%'.$keyword.'%')
			->orWhereHas('employer', function($q)use($keyword){
			 $q->where('employer_name', 'LIKE', '%'.$keyword.'%');
			})
			
                ->orWhereHas('recruitment_agent', function($q)use($keyword){
                $q->where('agent_first', 'LIKE', '%'.$keyword.'%');
                })
                
                ->orWhereHas('recruitment_agent', function($q)use($keyword){
                $q->where('agent_last', 'LIKE', '%'.$keyword.'%');
                })
			->paginate(15);

		$applicants->setPath(url().'/applicants/quick_search/'.$keyword);

		$employers = Cache::remember('employers',59, function()
		{
			return employer::all();
		});

		return view('quick_search')->withApplicants($applicants)->withKeyword($keyword)->withEmployers($employers);
	}

	public function all_employer(){
		return Cache::remember('all_employer_json',59, function()
		{
			$employers = employer::all();
			return $employers->toJson();
		});
	}

	public function all_country(){
		return Cache::remember('all_country',59, function()
		{
			$country = country::all();
			return $country->toJson();
		});
	}

	public function all_position(){
		return Cache::remember('all_position',59, function()
		{
			$position = position::all();
			return $position->toJson();
		});
	}

	public function get_notification_count_encodebranch(){

		return Cache::remember('get_notification_count_encodebranch',4, function()
		{
			$thecount = applicant::where('training_branches_id', '!=' ,0 )
			->where('applicant_status',10 )
			->whereHas('user_updated', function($q)
			{
			    $q->where('user_type', 12);
			})->count();
			return $thecount;
		 });
	}

	public function send_multiple_lineup(){
		//applicant_log
		//applicant
		$inputs = Request::all();

		$user = Session::get("user");

		foreach ($inputs['applicant_select'] as $applicant_id) {
			$applicant = applicant::find($applicant_id);
			$applicant->applicant_employer = $inputs['employer'];
			$applicant->applicant_status = 5;
			$applicant->save();

			$multi = new multiple_lineup;
			$multi->applicant_id = $applicant_id;
			$multi->applicant_employer = $inputs['employer'];
			$multi->save();

			$log = new applicant_log;
			$log->log_applicant = $applicant_id;
			$log->log_employer = $inputs['employer'];
			$log->log_status = 5;
			$log->log_country = $applicant->applicant_preferred_country;
			$log->log_date = date( 'Y-m-d', time() );
			$log->log_remarks = 'Send Applicant';
			$log->log_createdby = $user['user_id'];
			$log->log_created = date( 'Y-m-d H:i:s', time() );
			$log->save();
		}		

		return redirect(url().'/applicants/quick_search/'.$inputs['keyword']);
		// echo '<pre>';
		// print_r($inputs);
		// echo '</pre>';
		// die();
	}

}
