<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResumeAndCSSonSettings extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('settings')
            ->where('id', 4)
            ->update([
            	'key' => 'resumme',
            	'value' => 'benchstone_pdf.php',
            ]);
        DB::table('settings')->insert(
		    [
			    'key' => 'style', 
			    'value' => '.review-levelofhospital{display:none;} .review-bedcapacity{display:none;}'
		    ]
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('settings')
            ->where('id', 4)
            ->update([
            	'key' => 'clinnic',
            	'value' => 'KING FAISAL SPECIALIST HOSPITAL AND RESEARCH CENTRE RIYADH, KSA',
            ]);
        DB::table('settings')->where('key', 'style')->delete();
	}

}
