<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddREQUIREMENT extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		 Schema::table('applicant_requirement', function($table)
		{
		    $table->string('applicant_requirement_ecode')->before('requirement_visa');
			$table->string('applicant_requirement_paid')->before('requirement_visa');
			$table->string('applicant_requirement_rfp')->before('requirement_visa');
			$table->string('applicant_requirement_oec_expired')->before('requirement_visa');
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
		    $table->dropColumn('applicant_requirement_ecode');
			$table->dropColumn('applicant_requirement_paid');
			$table->dropColumn('applicant_requirement_rfp');
			$table->dropColumn('applicant_requirement_oec_expired');
		
		});
	}

}
