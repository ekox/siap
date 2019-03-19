<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Guard;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }
	
	/**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    /*public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if(!session('authenticated')){
                if ($request->ajax() || $request->wantsJson()) {
                    return '<script>window.location.href="/";</script>';
                } else {
                    return redirect()->guest('login');
                }
            }
        }

        return $next($request);
    }*/
	
	public function handle($request, Closure $next)
    {
        if ($this->auth->guest()) {
            //cek apakah ada sesi
			if(session('authenticated')==null){
				if ($request->ajax()) {
					return '<script>window.location.href="auth/logout";</script>';
				} else {
					return redirect()->guest('auth');
				}
			}
        }

        return $next($request);
    }
	
}
