<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ApplicantTableMotherAndChildColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant', function($table)
		{
		    $table->string('applicant_children')->after('currency');
		    $table->string('applicant_mothers')->after('currency');
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
		    $table->dropColumn('applicant_children');
		    $table->dropColumn('applicant_mothers');
		});
	}

}
