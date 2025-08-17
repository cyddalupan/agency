<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableSurverAlphatomo extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('survey_alphatomo', function($table)
		{
		    $table->increments('id');
		    $table->integer('applicant_id');
		    $table->boolean('do_you_have_any_experience_of_taking_care_of_pets');
		    $table->boolean('are_you_willing_to_work_in_a_house_where_there_are_pets');
		    $table->boolean('work_without_any_day_off');
		    $table->boolean('are_you_willing_to_work_with_muslim_family');
		    $table->boolean('are_you_willing_to_work_with_non_muslim_family');
			$table->boolean('are_you_willing_not_to_use_mobile_during_working_hours');
		    $table->boolean('do_you_drink_alcohol_or_smoke');
		    $table->boolean('have_you_ever_suffered_from_any_serious_illness');
		    $table->boolean('will_you_be_honest_and_loyal_to_your_employer');
		    $table->boolean('do_you_promise_not_to_use_mobile_without_permission');
		    $table->boolean('are_you_aware_of_2_years_employment_contract_with_your_employer');
		    $table->boolean('promise_not_to_answer_back_to_employer');
		    $table->boolean('promise_not_invite_friends');
		    $table->boolean('are_your_fam_allowerd_you_to_work_as_housemaid_in_malaysia');
		    $table->boolean('ready_to_be_separated_from_family_in_malaysia');
		    $table->boolean('physically_and_mentally_as_housemaid_in_malaysia');
		    $table->boolean('newborn_baby_exp');
		    $table->boolean('newborn_baby_will');
		    $table->boolean('toddles_exp');
		    $table->boolean('toddles_will');
		    $table->boolean('baby_care_exp');
		    $table->boolean('baby_care_will');
		    $table->boolean('child_care_exp');
		    $table->boolean('child_care_will');
		    $table->boolean('special_child_exp');
		    $table->boolean('special_child_will');
		    $table->boolean('care_of_disable_exp');
		    $table->boolean('care_of_disable_will');
		    $table->boolean('care_of_bedridden_exp');
		    $table->boolean('care_of_bedridden_will');
		    $table->boolean('elderly_care_exp');
		    $table->boolean('elderly_care_will');
		    $table->boolean('cooking_exp');
		    $table->boolean('cooking_will');
		    $table->boolean('laundry_exp');
		    $table->boolean('laundry_will');
		    $table->boolean('ironing_exp');
		    $table->boolean('ironing_will');
		    $table->boolean('care_of_pets_exp');
		    $table->boolean('care_of_pets_will');
		    $table->boolean('car_wash_exp');
		    $table->boolean('car_wash_will');
		    $table->boolean('gardening_exp');
		    $table->boolean('gardening_will');
		    $table->text('experience_in_taking_care_of_baby');
		    $table->text('experience_in_taking_care_of_children');
		    $table->text('experience_in_taking_care_of_old');
		    $table->text('future_plans');
		    $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('survey_alphatomo');
	}

}
