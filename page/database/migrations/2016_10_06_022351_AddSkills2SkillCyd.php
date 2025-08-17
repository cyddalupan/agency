<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSkills2SkillCyd extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applicant_skills_cyds', function($table)
		{
			$table->boolean('arabic_cooking')->after('computer');
			$table->boolean('baby_sitting')->after('computer');
			$table->boolean('children_care')->after('computer');
			$table->boolean('tutoring')->after('computer');
			$table->boolean('cleaning')->after('computer');
			$table->boolean('washing')->after('computer');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

		Schema::table('applicant_skills_cyds', function($table)
		{
			$table->dropColumn('arabic_cooking');
			$table->dropColumn('baby_sitting');
			$table->dropColumn('children_care');
			$table->dropColumn('tutoring');
			$table->dropColumn('cleaning');
			$table->dropColumn('washing');
		});
	}

}
