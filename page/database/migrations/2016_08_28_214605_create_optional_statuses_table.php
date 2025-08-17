<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\OptionalStatus;

class CreateOptionalStatusesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('optional_statuses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('optional_status');
			$table->timestamps();
		});

		$status = new OptionalStatus;
		$status->optional_status = "Waiting for Visa";
		$status->save();

		$status = new OptionalStatus;
		$status->optional_status = "Waiting for Contract";
		$status->save();
		
		$status = new OptionalStatus;
		$status->optional_status = "Fit to work";
		$status->save();
		
		$status = new OptionalStatus;
		$status->optional_status = "Unfit for Visa";
		$status->save();
		
		$status = new OptionalStatus;
		$status->optional_status = "For booking";
		$status->save();
		
		$status = new OptionalStatus;
		$status->optional_status = "Backout/cancelled";
		$status->save();

		Schema::table('applicant', function($table)
		{
		    $table->integer('optional_statuses_id')->after('training_branches_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('optional_statuses');

		Schema::table('applicant', function($table)
		{
		    $table->dropColumn('optional_statuses_id');
		});
	}

}
