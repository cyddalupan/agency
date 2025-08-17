<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\currency;

class AddCurrency extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$currency = new currency;
		$currency->currency = 'United States dollar';
		$currency->save();

		$currency = new currency;
		$currency->currency = 'Euro';
		$currency->save();

		$currency = new currency;
		$currency->currency = 'Japanese yen';
		$currency->save();

		$currency = new currency;
		$currency->currency = 'Philippine Peso';
		$currency->save();

		$currency = new currency;
		$currency->currency = 'Hong Kong dollar';
		$currency->save();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
