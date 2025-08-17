<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VisaDurationApplicantRequiremnts extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_requirement', function($table)
		{
		    $table->integer('visa_duration');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('applicant_requirement', function($table)
		{
		    $table->dropColumn('visa_duration');
		});
	}

}
