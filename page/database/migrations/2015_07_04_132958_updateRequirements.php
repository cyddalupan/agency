<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRequirements extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		Schema::table('applicant_requirement', function($table)
		{
			$table->string('offer_letter')->after('requirement_school_records');
			$table->date('oec_expired')->after('requirement_oec_release_date');
			$table->string('ticket_no')->after('requirement_ticket');
			$table->date('flight_date')->after('requirement_ticket');
			$table->string('ticket_remarks')->after('requirement_ticket');
		});
		//
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
