<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApplicantsAddHospitalLevel extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_experiences', function($table)
		{
			$table->string('hospital_level')->after('experience_salary');
		});
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
		    $table->dropColumn('hospital_level');
		});
	}

}
