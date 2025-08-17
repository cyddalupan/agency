<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ImproveSettings extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('settings')->where('id',5)->delete();
		DB::table('settings')
            ->where('id', 4)
            ->update(['key' => 'style', 'value' => '.customclass{display:none;}']);
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
            ->update(['key' => 'resumme', 'value' => 'abba_pdf.php']);
        DB::table('settings')
            ->where('id', 5)
            ->update(['key' => 'style', 'value' => '.customclass{display:none;}']);
	}

}
