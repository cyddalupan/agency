<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Certification extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_certificate', function($table)
		{
			$table->string('certificate_tor')->after('certificate_m1b');
			$table->string('certificate_prc_cert')->after('certificate_m1b');
			$table->string('certificate_prc_id')->after('certificate_m1b');
			$table->string('certificate_prc_rating')->after('certificate_m1b');
			$table->string('certificate_coe')->after('certificate_m1b');
			$table->string('certificate_bc')->after('certificate_m1b');
			$table->string('certificate_mc')->after('certificate_m1b');
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
