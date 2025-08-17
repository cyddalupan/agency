<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableAlphatomo extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('applicants_alphatomo', function($table)
		{
		    $table->increments('id');
		    $table->integer('applicant_id');
		    $table->string('pos_in_fam');
		    $table->string('no_of_bro');
		    $table->string('no_of_sis');
		    $table->string('nam_of_fat');
		    $table->string('occ_of_fat');
			$table->string('occ_of_mom');
		    $table->string('relative_name');
		    $table->string('relative_mobile');
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
		Schema::drop('applicants_alphatomo');
	}

}
