<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicantSkillsCydsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('applicant_skills_cyds', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('applicant_id');
			$table->boolean('ironing');
			$table->boolean('cooking');
			$table->boolean('sewing');
			$table->boolean('computer');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('applicant_skills_cyds');
	}

}
