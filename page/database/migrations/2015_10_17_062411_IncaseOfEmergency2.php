<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncaseOfEmergency2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant', function($table)
		{
			$table->string('applicant_incase_address')->after('applicant_source');
			$table->string('applicant_incase_contact')->after('applicant_source');
			$table->string('applicant_incase_relation')->after('applicant_source');
			$table->string('applicant_incase_name')->after('applicant_source');
			
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('applicant', function($table)
		{
		    $table->dropColumn('applicant_incase_address');
			$table->dropColumn('applicant_incase_contact');
			$table->dropColumn('applicant_incase_relation');
			$table->dropColumn('applicant_incase_name');
		});
	}

}
