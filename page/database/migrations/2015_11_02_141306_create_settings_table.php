<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('settings', function(Blueprint $table)
		{
			$table->increments('id');
		    $table->string('key')->unique();
		    $table->text('value');
			$table->timestamps();
		});

		Setting::create([
        	'key' 	=> 'client',
        	'value' => 'BENCHSTONE'
        ]);

        Setting::create([
        	'key' 	=> 'client_full',
        	'value' => 'BENCHSTONE ENTERPRISES, INC'
        ]);

        Setting::create([
        	'key' 	=> 'icon_link',
        	'value' => 'assets/images/admin/logo.png'
        ]);

        Setting::create([
        	'key' 	=> 'clinnic',
        	'value' => 'KING FAISAL SPECIALIST HOSPITAL AND RESEARCH CENTRE RIYADH, KSA'
        ]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('settings');
	}

}
