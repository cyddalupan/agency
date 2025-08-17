<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Additionalfields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		Schema::table('applicant_experiences', function($table)
		{
			if (!Schema::hasColumn('applicant_experiences', 'NoFamilyMembers'))
		    	$table->string('NoFamilyMembers')->after('experience_country');
			if (!Schema::hasColumn('applicant_experiences', 'nationality'))
		    	$table->string('nationality')->after('experience_country');
			if (!Schema::hasColumn('applicant_experiences', 'typeOfResidence'))
		    	$table->string('typeOfResidence')->after('experience_country');
			if (!Schema::hasColumn('applicant_experiences', 'salary'))
		    	$table->string('salary')->after('experience_country');
			if (!Schema::hasColumn('applicant_experiences', 'reasonOfLeaving'))
		    	$table->string('reasonOfLeaving')->after('experience_country');


	    	$table->string('extraExperience10')->after('reasonOfLeaving');
	    	$table->string('extraExperience11')->after('reasonOfLeaving');
	    	$table->string('extraExperience12')->after('reasonOfLeaving');
    		$table->string('bed_capacity')->change();
    		$table->string('experience_salary')->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('applicant_experiences', function($table)
		{
			if (Schema::hasColumn('applicant_experiences', 'NoFamilyMembers'))
			    $table->dropColumn('NoFamilyMembers');
			if (Schema::hasColumn('applicant_experiences', 'nationality'))
			    $table->dropColumn('nationality');
			if (Schema::hasColumn('applicant_experiences', 'typeOfResidence'))
			    $table->dropColumn('typeOfResidence');
			if (Schema::hasColumn('applicant_experiences', 'salary'))
			    $table->dropColumn('salary');
			if (Schema::hasColumn('applicant_experiences', 'reasonOfLeaving'))
			    $table->dropColumn('reasonOfLeaving');
			
		    $table->dropColumn('extraExperience10');
		    $table->dropColumn('extraExperience11');
		    $table->dropColumn('extraExperience12');

    		$table->integer('bed_capacity')->change();
    		$table->float('experience_salary')->change();
		});
	}

}
