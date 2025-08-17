<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CertificationDHA extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_certificate', function($table)
		{
			$table->string('certificate_dha')->after('certificate_ielts');
			
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
		    $table->dropColumn('certificate_dha');
		});
	}

}
