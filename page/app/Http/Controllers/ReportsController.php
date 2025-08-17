<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\applicant;
use App\position;
use App\applicant_log;
use App\recruitment_agent;

class ReportsController extends Controller {

	/**
	 * Get all applicant Counts of Every Position
	 * 
	 * Get all positions
	 * count applicants for every position
	 * dont include zero applicant, position
	 * convert to json
	 */
	public function selected_count_positions($employer_id)
	{
		$position = position::has('applicant')->get();
		$select_count[][] = 0;

		foreach ($position as $position_key => $position_value) {
			$count = 0;
			foreach ($position_value->applicant as $applicant_key => $applicant_value) {
				if($applicant_value->applicant_status == 4){
					if($employer_id == 0){
						$count++;
					}else{
						if($applicant_value->applicant_employer == $employer_id){
							$count++;
						}
					}
				}
			}

			//make sure applicant in the position is not empty
			if($count > 0){
				$select_count[$position_key]['count'] = $count;
				$select_count[$position_key]['name'] = $position_value->position_name;
			}
		}
		return json_encode($select_count);
	}

	public function deployed_with_position($employer_id)
	{
		$return['count_weekly'] = 0;
		$return['count_monthly'] = 0;
		$return['count_yearly'] = 0;

		$applicants = applicant::deployed();
		if($employer_id != 0){
			$applicants->where('log_employer',$employer_id);
		}
		$applicants = $applicants->get();

		foreach ($applicants as $applicant) {
			if(isset($applicant->log->last()->log_date)){
				$date_updated = date('Ymd', strtotime($applicant->log->last()->log_date));
				
				//test weekly
				$week_end_date = date('Ymd',strtotime("-1 week"));
				if($date_updated > $week_end_date){
					$return['count_weekly']++;

					$return['jobs_weekly'][$applicant->position->position_name]['name'] = $applicant->position->position_name;
					if(isset($return['jobs_weekly'][$applicant->position->position_name]['count']))
						$return['jobs_weekly'][$applicant->position->position_name]['count']++;
					else
						$return['jobs_weekly'][$applicant->position->position_name]['count'] = 1;
				}

				//test weekly
				$month_end_date = date('Ymd',strtotime("-1 month"));
				if($date_updated > $month_end_date){
					$return['count_monthly']++;

					$return['jobs_monthly'][$applicant->position->position_name]['name'] = $applicant->position->position_name;
					if(isset($return['jobs_monthly'][$applicant->position->position_name]['count']))
						$return['jobs_monthly'][$applicant->position->position_name]['count']++;
					else
						$return['jobs_monthly'][$applicant->position->position_name]['count'] = 1;
				}

				//test Yealy
				$year_end_date = date('Ymd',strtotime("-1 year"));
				if($date_updated > $year_end_date){
					$return['count_yearly']++;

					$return['jobs_yearly'][$applicant->position->position_name]['name'] = $applicant->position->position_name;
					if(isset($return['jobs_yearly'][$applicant->position->position_name]['count']))
						$return['jobs_yearly'][$applicant->position->position_name]['count']++;
					else
						$return['jobs_yearly'][$applicant->position->position_name]['count'] = 1;	
				}
			}
		}

		echo json_encode($return);

	}

	public function lineup_with_position($employer_id)
	{
		$return['count_monthly'] = 0;
		$return['count_yearly'] = 0;

		$applicants = applicant::lineup();
		if($employer_id != 0){
			$applicants->where('applicant_employer',$employer_id);
		}
		$applicants = $applicants->get();

		foreach ($applicants as $applicant) {
			if(isset($applicant->log->last()->log_date)){
				$date_updated = date('Ymd', strtotime($applicant->log->last()->log_date));

				//test weekly
				$month_end_date = date('Ymd',strtotime("-1 month"));
				if($date_updated > $month_end_date){
					$return['count_monthly']++;

					$return['jobs_monthly'][$applicant->position->position_name]['name'] = $applicant->position->position_name;
					if(isset($return['jobs_monthly'][$applicant->position->position_name]['count']))
						$return['jobs_monthly'][$applicant->position->position_name]['count']++;
					else
						$return['jobs_monthly'][$applicant->position->position_name]['count'] = 1;
				}
				//test Yealy
				$year_end_date = date('Ymd',strtotime("-1 year"));
				if($date_updated > $year_end_date){
					$return['count_yearly']++;

					$return['jobs_yearly'][$applicant->position->position_name]['name'] = $applicant->position->position_name;
					if(isset($return['jobs_yearly'][$applicant->position->position_name]['count']))
						$return['jobs_yearly'][$applicant->position->position_name]['count']++;
					else
						$return['jobs_yearly'][$applicant->position->position_name]['count'] = 1;	
				}
			}
			
		}

		echo json_encode($return);
	}

	public function summary_applied_online($agent_id){

		$return['count_weekly'] = 0;
		$return['count_monthly'] = 0;
		$return['count_yearly'] = 0;

		$year_end_date 	= date('Y-m-d h:i:s',strtotime("-1 year"));
		$month_end_date = date('Y-m-d h:i:s',strtotime("-1 month"));
		$week_end_date 	= date('Y-m-d h:i:s',strtotime("-1 week"));

		if($agent_id == 0){
			$recruitment_agents = recruitment_agent::all();

			//add direct hire
			$recruitment_agents->offsetSet(count($recruitment_agents), (object) array(
				'agent_id' => '0', 
				'agent_first' => 'DIRECT', 
				'agent_last' => 'HIRE', 
			));
		}else{
			$recruitment_agents = recruitment_agent::where('agent_id',$agent_id)->get();
		}

		foreach ($recruitment_agents as $recruitment_agent) {

			$weekcount = applicant::where('applicant_source', $recruitment_agent->agent_id)
									->where('applicant_date_applied', '>=', $week_end_date)
									->where('applicant_date_applied', '<=', date('Y-m-d h:i:s'))
									->count();
			if($weekcount > 0){
				$return['weekly'][$recruitment_agent->agent_id]['count'] = $weekcount;
				$return['weekly'][$recruitment_agent->agent_id]['name'] =  $recruitment_agent->agent_first.' '.$recruitment_agent->agent_last;
				$return['count_weekly'] = $return['count_weekly'] + $return['weekly'][$recruitment_agent->agent_id]['count'];
			}

			$monthcount = applicant::where('applicant_source', $recruitment_agent->agent_id)
									->where('applicant_date_applied', '>=', $month_end_date)
									->where('applicant_date_applied', '<=', date('Y-m-d h:i:s'))
									->count();
			if($monthcount > 0){
				$return['monthly'][$recruitment_agent->agent_id]['count'] = $monthcount;
				$return['monthly'][$recruitment_agent->agent_id]['name'] =  $recruitment_agent->agent_first.' '.$recruitment_agent->agent_last;
				$return['count_monthly'] = $return['count_monthly'] + $return['monthly'][$recruitment_agent->agent_id]['count'];
			}

			$yearcount = applicant::where('applicant_source', $recruitment_agent->agent_id)
									->where('applicant_date_applied', '>=', $year_end_date)
									->where('applicant_date_applied', '<=', date('Y-m-d h:i:s'))
									->count();
			if($yearcount > 0){
				$return['yearly'][$recruitment_agent->agent_id]['count'] = $yearcount;
				$return['yearly'][$recruitment_agent->agent_id]['name'] =  $recruitment_agent->agent_first.' '.$recruitment_agent->agent_last;
				$return['count_yearly'] = $return['count_yearly'] + $return['yearly'][$recruitment_agent->agent_id]['count'];
			}
		}
		echo json_encode($return);
		
	}

}