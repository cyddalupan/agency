<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTraining2Applicant extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant', function($table)
		{
		    $table->integer('training_branches_id')->after('applicant_slug');
		    $table->date('start_training_at')->after('applicant_slug');
		    $table->date('end_training_at')->after('applicant_slug');
		    $table->string('training_remarks')->after('applicant_slug');
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
		    $table->dropColumn('training_branches_id');
		    $table->dropColumn('start_training_at');
		    $table->dropColumn('end_training_at');
		    $table->dropColumn('training_remarks');
		});
	}

}
