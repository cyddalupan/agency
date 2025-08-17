<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBedCapacity extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_experiences', function($table)
		{
		    $table->integer('bed_capacity')->after('hospital_level');
		});

		$applicant_experiences = DB::table('applicant_experiences')->get();

		foreach ($applicant_experiences as $applicant_experience)
		{
		    DB::table('applicant_experiences')
		    	->where('experience_id', $applicant_experience->experience_id)
            	->update(['bed_capacity' => $applicant_experience->experience_salary]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('applicant_experiences', function($table)
		{
		    $table->dropColumn('bed_capacity');
		});
	}

}
