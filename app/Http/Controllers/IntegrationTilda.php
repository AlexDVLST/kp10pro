<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Log;

class IntegrationTilda extends Controller
{
    public function register(Request $request)
    {
        $data          = $request->all();
        $data['name']  = 'Имя Фамилия';
        $data['tilda'] = true;
        //TODO: реалізувати перевірку з якого сервака був запит. Дозволити тільки для Tilda
    
        return (new RegisterController())->register(new Request($data));
    }
}
