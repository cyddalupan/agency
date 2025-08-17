<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCertificateNoMarriage extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		
		Schema::table('applicant_certificate', function($table)
		{
			$table->date('applicant_certificate_no_marriage')->after('certificate_prc_id');
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
		    $table->dropColumn('applicant_certificate_no_marriage');
		});
	}

}
