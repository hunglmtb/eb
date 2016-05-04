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
		if ($user&&$user->hasRight($right)) {
			return $next($request);		
		}
		return response('Unauthorized:You has not right to access', 401);
	}
}
