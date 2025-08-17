<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRepatOnApplicantsAndLogs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant', function($table)
		{
		    $table->dateTime('repat_date')->after('applicant_incase_address');
		    $table->boolean('is_repat')->after('applicant_incase_address');
		});

		Schema::table('applicant_log', function($table)
		{
		    $table->dateTime('repat_date')->after('log_country');
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
		    $table->dropColumn('repat_date');
		    $table->dropColumn('is_repat');
		});

		Schema::table('applicant_log', function($table)
		{
		    $table->dropColumn('repat_date');
		});
	}

}
