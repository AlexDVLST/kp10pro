<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Help;

class HelpController extends Controller
{
    public function videos()
    {
        
        return response()->json(Help::all());

    }
}
