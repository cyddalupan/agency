<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JobOfferDate extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_requirement', function($table)
		{
			$table->date('requirement_job_accepted')->after('requirement_job_offer');
			$table->date('requirement_job_received')->after('requirement_job_offer');
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
		    $table->dropColumn('requirement_job_accepted');
		    $table->dropColumn('requirement_job_received');
		});
	}

}
