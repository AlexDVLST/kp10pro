<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegistered;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($account)
    {

        // Mail::to('nnnicolay@fex.net')->send(new UserRegistered(['email' => 'email', 'domain' => 'company', 'password' => 'password']));

        $page = Page::whereSlug('home')->first();

        $user = Auth::user();

        return view('pages.home', ['user' => $user, 'page' => $page]);
    }

    public function dismissTour()
    {
        UserMeta::updateMeta('show-tour', 0);
    }
}
