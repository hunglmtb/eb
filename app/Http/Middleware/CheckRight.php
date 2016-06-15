<?php namespace App\Http\Middleware;

use Closure;

class CheckRight {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next,$right)
	{
		// Perform action
		$user = auth()->user();
		if ($user&&$user->containRight($right)) {
			return $next($request);		
		}
		if ($request->ajax()) return response('Unauthorized:You has not right to access', 401);
		else  return view ( 'core.unauthorized');
	}
}
