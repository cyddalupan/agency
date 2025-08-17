<?php namespace App\Http\Middleware;

use Closure;

class ApplicantLoginMiddleware {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if(null !== session('applicant_id') ){
			return $next($request);	
		}else{
			return redirect('applicant/login');
		}
	}

}
