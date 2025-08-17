<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Typeexam extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_certificate', function($table)
		{
			$table->string('certificate_prc_type')->after('certificate_prc_rating');
			
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('applicant_certificate', function($table)
		{
		    $table->dropColumn('certificate_prc_type');
		});
	}

}
