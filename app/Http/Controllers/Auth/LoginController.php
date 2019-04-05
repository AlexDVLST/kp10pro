<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Order;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware(['check.domain', 'guest'])->except(['logout', 'loginAfterRegistered']);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm($account = '')
    {
        return view('auth.login', ['account' => $account]);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password'        => 'required|string',
            'domain'          => 'required|string|max:100'
        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password', 'domain');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  mixed $user
     *
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //Current paid licenses
        $licenses = Order::whereAccountId($user->accountId)->first()->licenses;
        //Get employees
        $employees = User::role('employee')->whereDomain($user->domain)->get();
        $online    = 0; //1 - for current user

        if ($employees) {
            $employees->each(function ($employee) use (&$online, $user) {
                $session = DB::table('sessions')
                    ->whereUserId($employee->id)
                    ->where('last_activity', '>', Carbon::now()->subMinutes(config('session.lifetime'))->timestamp)
                    ->first();
                if ($session) {
                    if ($employee->id != $user->id) {
                        $online += 1;
                    }
                }
            });

            //
            if ($online >= $licenses) {
                Auth::logout();
                return redirect('login')->with('license', __('messages.licenses.count.error'));
            }
        }
    }

    /**
     * Login after user registered
     *
     * @param string $account
     * @param Request $request
     * @return void
     */
    public function loginAfterRegistered($account, Request $request)
    {
        if (!Auth::check()) {
            $users = User::whereDomain($account)->get();
            
            if ($users->isNotEmpty() && $users->count() == 1 && !$users->first()->remember_token) {
                //Auth user with id
                Auth::loginUsingId($users->first()->id, true);
            }
        }
        return redirect('/');
    }
}
