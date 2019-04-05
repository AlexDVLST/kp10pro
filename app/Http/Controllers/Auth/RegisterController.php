<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\UserMeta;
use App\Mail\UserRegistered;
use App\Helpers\UserHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use App\Mail\AdminUserRegistered;
use App\Jobs\PolytellEmailParserJob;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'nullable|phone' //required!
        ]);
        //Find user with same email with role user (account admin)
        $user = User::role('user')->whereEmail($data['email'])->first();

        $validator->after(function ($validator) use ($user) {
            if ($user) {
                $validator->errors()->add('email', __('messages.register.email.exist'));
            }
        });

        return $validator;

        // return Validator::make($data, [
        //     'domain'      => 'required|string|max:100|unique:users|not_in:admin,administrator',
        //     'surname'     => 'required|string|max:255',
        //     'name'          => 'required|string|max:255',
        //     'middle-name' => 'nullable|string|max:255',
        //     'email'         => 'required|string|email|max:255',
        //     'phone'         => 'required|phone'
        //     'password'   => 'required|string|min:6|confirmed',
        // ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $data['domain'] = UserHelper::generateDomain();
        $data['password'] = str_random(8); // generate password (8 symbol)
        $nameData = explode(' ', $data['name']); //split by space
        $name = $nameData[0];
        $surname = isset($nameData[1]) ? $nameData[1] : $nameData[0];
        $middleName = '';
        //Parse phone number
        $data['phone'] = preg_replace('/[^0-9]/', '', isset($data['phone']) ? $data['phone'] : '');
        $data['name'] = $name;
        $data['surname'] = $surname;

        $user = User::create([
            'domain' => $data['domain'],
            'surname' => $surname,
            'name' => $name,
            'middle_name' => $middleName,
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $signature = $name . ' ' . $surname . "\n"
            . 'Директор' . "\n"
            . $data['email'] . "\n"
            . $data['phone'];
        //
        $user->phoneRelation()->create(['phone' => $data['phone']]);
        $user->signatureRelation()->create(['signature' => $signature]);
        //For email
        $user->originalPassword = $data['password'];

        return $user;
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // $this->guard()->login($user);
        //Create job emailParser
        PolytellEmailParserJob::dispatch($user, 0, $request->get('referer'));

        if ($request->ajax() || $request->get('tilda')) {
            return response()->json(env('APP_PROTOCOL') . $user->domain . '.' . env('APP_DOMAIN') . '/login-after-registered');
        } else {
            return $this->registered($request, $user)
                ? : redirect($this->redirectPath());
        }
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        return redirect(env('APP_PROTOCOL') . $user->domain . '.' . env('APP_DOMAIN') . '/login-after-registered');
    }
}
