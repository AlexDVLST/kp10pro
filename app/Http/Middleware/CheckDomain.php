<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class CheckDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //If login from main domain
        if (preg_match("/^(http:\/\/|https:\/\/)" . env('APP_DOMAIN') . "/", $request->url())) {
            Auth::logout();
            return redirect(Config::get('app.url'));
        }

        //Check domain
//        $host     = $request->getHost();
//        $hostData = explode('.', $host);
//        if (count($hostData) === 3) {
//            //Get domain
//            $domain = $hostData[0];
//            //If domain not exists
//            if (!User::whereDomain($domain)->count()) {
//
//            }
//        }


        return $next($request);
    }
}
