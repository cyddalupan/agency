<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class convertAlphatomoDb extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'convert:alphatomo';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Move alphatomo database to custom_fields_data table';


	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->info('Script Start!');

		$count = 0;

		//localize array
		$combination = array(
			array("do_you_have_any_experience_of_taking_care_of_pets",6),
			array("are_you_willing_to_work_in_a_house_where_there_are_pets",7), 
			array("work_without_any_day_off",8),
			array("are_you_willing_to_work_with_muslim_family",9),
			array("are_you_willing_to_work_with_non_muslim_family",10),
			array("are_you_willing_not_to_use_mobile_during_working_hours",11),
			array("do_you_drink_alcohol_or_smoke",12),
			array("have_you_ever_suffered_from_any_serious_illness",13),
			array("will_you_be_honest_and_loyal_to_your_employer",14),
			array("do_you_promise_not_to_use_mobile_without_permission",15),
			array("are_you_aware_of_2_years_employment_contract_with_your_employer",16),
			array("promise_not_to_answer_back_to_employer",17),
			array("promise_not_invite_friends",18),
			array("are_your_fam_allowerd_you_to_work_as_housemaid_in_malaysia",19),
			array("ready_to_be_separated_from_family_in_malaysia",20),
			array("physically_and_mentally_as_housemaid_in_malaysia",21)
		);

		$ability = array(
			array("newborn_baby_exp","newborn_baby_will",47),
			array("toddles_exp","toddles_will",48),
			array("baby_care_exp","baby_care_will",49),
			array("child_care_exp","child_care_will",50),
			array("special_child_exp","special_child_will",51),
			array("care_of_disable_exp","care_of_disable_will",52),
			array("care_of_bedridden_exp","care_of_bedridden_will",53),
			array("elderly_care_exp","elderly_care_will",54),
			array("cooking_exp","cooking_will",55),
			array("laundry_exp","laundry_will",56),
			array("ironing_exp","ironing_will",57),
			array("care_of_pets_exp","care_of_pets_will",58),
			array("car_wash_exp","car_wash_will",59),
			array("gardening_exp","gardening_will",60)
		);

		$varcharinputs = array(
			array("experience_in_taking_care_of_baby",44),
			array("experience_in_taking_care_of_children",45), 
			array("experience_in_taking_care_of_old",46),
			array("future_plans",78)
		);

		$survey = \DB::table('survey_alphatomo')->take(20)->get();
		foreach ($survey as $data)
		{
			$count++;

			//for survey
			foreach ($combination as $key => $combi) {
				if($data->$combi[0] == 1)
					$value = "yes";
				else
					$value = "no";

				$this->insertConverted($data->applicant_id,$combi[1],$value);
			}

			//for working ability
			foreach ($ability as $key => $abil) {

				if($data->$abil[0] == 1){
					if($data->$abil[1] == 1){
						$insert = "willing and with experience";
					}else{
						$insert = "With experience only";
					}
				}else{
					if($data->$abil[1] == 1){
						$insert = "willing only";
					}else{
						$insert = "no experience and not willing";
					}
				}

				$this->insertConverted($data->applicant_id,$abil[2],$insert);
			}

			//for experience and future plans
			foreach ($varcharinputs as $key => $varcharinput) {
				$insert = $data->$varcharinput[0];
				$this->insertConverted($data->applicant_id,$varcharinput[1],$insert);
			}

			$this->info($this->get_percentage(count($survey), $count)."%");
		}

		$this->info('Done!');
	}

	public function insertConverted($applicant_id,$customFieldId,$value){
		\DB::table('custom_field_values')->insert([
			'customFieldId' => $customFieldId, 
			'applicantID' => $applicant_id,
			'value' => $value,
		]);
	}

	public function get_percentage($total, $number)
	{
		if ( $total > 0 ) {
			return round($number / ($total / 100),2);
		} else {
			return 0;
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
		];
	}

}
