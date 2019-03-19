<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	/*public function handle($request, Closure $next)
	{
		return parent::handle($request, $next);
	}*/
	
	//add an array of Routes to skip CSRF check
	private $openRoutes = ['login', 'api/v1/*', 'auth'];
    
	//modify this function
	public function handle($request, Closure $next)
	{
		//add this condition 
		foreach($this->openRoutes as $route) {

			if ($request->is($route)) {
				return $next($request);
			}
		}
		
		return parent::handle($request, $next);
	}
}