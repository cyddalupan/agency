<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApplicantReqAdd extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		 Schema::table('applicant_requirement', function($table)
		{
		    $table->string('applicant_requirement_visaremarks')->after('requirement_visa');
			
		  
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
		    $table->dropColumn('applicant_requirement_visaremarks');
		
		});
	}

}
