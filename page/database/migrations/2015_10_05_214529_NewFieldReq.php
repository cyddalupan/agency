<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewFieldReq extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_requirement', function($table)
		{
		    $table->date('requirement_visa_stamp')->after('requirement_visa_date');
		  
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
		    $table->dropColumn(['requirement_visa_stamp']);
		});
	}

}
