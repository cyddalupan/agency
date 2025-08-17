<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateApplicantCertificate extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::table('applicant_certificate', function($table)
		{
			$table->date('nbi_expired_date')->after('certificate_authenticated_nbi');
			$table->date('red_ribbon_expired_date')->after('certificate_authenticated');
			$table->date('red_ribbon_file_date')->after('certificate_authenticated');
			$table->date('insurance_date')->after('certificate_insurance');
			$table->string('insurance_no')->after('certificate_insurance');
		});
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
