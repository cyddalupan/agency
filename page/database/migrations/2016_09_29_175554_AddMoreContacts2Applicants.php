<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreContacts2Applicants extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant', function($table)
		{
		    $table->string('applicant_contacts_3')->after('applicant_contacts');;
		    $table->string('applicant_contacts_2')->after('applicant_contacts');;
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
		    $table->dropColumn('applicant_contacts_2');
		    $table->dropColumn('applicant_contacts_3');
		});
	}

}
