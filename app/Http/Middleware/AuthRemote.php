<?php

namespace App\Http\Middleware;

use App\Models\IntegrationBitrix24;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\IntegrationAmocrm;
use App\Models\IntegrationMegaplan;
use App\Models\IntegrationMegaplanUser;
use Illuminate\Support\Facades\Log;

class AuthRemote
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->input('auth') === 'remote') {
            //Logout current user
            Auth::logout();
            //Get type of the auth
            $type = $request->input('type');
            //Amocrm
            if ($type == 'amocrm') {
                $amohash    = $request->input('amohash'); //api_token
                $domain     = $request->input('domain'); //host
                $amouser_id = $request->input('amouser_id');
                
                if ($amohash && $domain && $amouser_id) {
                    //Find integration data
                    $amocrm = IntegrationAmocrm::with('amocrmUsers.user')->whereHost($domain)
                        ->whereApiToken($amohash)->first();
                        
                    //If user exist
                    if ($amocrm) {
                        $check = false;
                        $amocrm->amocrmUsers->each(function ($amoUser) use ($amouser_id, &$check) {
                            //Find same user
                            if ($amoUser->amocrm_user_id == $amouser_id) {
                                //Get user
                                $user = $amoUser->user;
                                //Auth user with id
                                Auth::loginUsingId($user->id, true);
                                $check = true;
                            }
                        });
                        //User found
                        if ($check) {
                            return redirect($request->url());
                        }
                    }
                }
            }

// https://local.kp10.local/editor/401?auth=remote&type=megaplan&token=NTE0MjVlMThhZjEzMDE0OTE4MmUxNmU0NTE2NGYyNzBlMWRhZDY2ZTMxMmI3ZWU4NWI0NjE1N2Y2YmE0ZDBkZQ&uid=1000032

            // Log::debug($request->input());

            // Megaplan
            if ($type == 'megaplan') {
                $token = $request->input('token');
                $uid   = $request->input('uid');

                $MegaplanInfo = IntegrationMegaplan::whereApiToken($token)->first();
                if ( $MegaplanInfo ) {
                    $MegaplanUser = IntegrationMegaplanUser::whereMegaplanUserId($uid)->whereAccountId($MegaplanInfo->account_id)->first();

                    // Log::debug( print_r($MegaplanInfo,1) );
                    // Log::debug( print_r($MegaplanUser,1) );

                    $check = false;
                    //Find same user
                    if ($MegaplanUser) {
                        //Auth user with id
                        Auth::loginUsingId($MegaplanUser->user_id, true);

                        $check = true;
                    }
                    
                    //User found
                    if ($check) {
                        // dd(Auth::user());
                        return redirect($request->url());
                    }
                }

                // $MegaplanInfo

            }

            // Bitrix24
            if ($type == 'bitrix24') {
                $userId = $request->input('uid');
                $userToken = $request->input('token');

                $user = User::whereId($userId)->first();

                $userAccess = false;
                if($user && $user->tokens){
                    $tokens = json_decode($user->tokens, true);
                    foreach ($tokens as $token){
                        if($token['name'] == $type && $token['id'] == $userToken && $token['user_id'] == $userId){
                            $userAccess = true;
                        }
                    }
                }

                if($userAccess){
                    Auth::loginUsingId($userId, true);
                    return redirect($request->url());
                }
            }
        }
        return $next($request);
    }
}
