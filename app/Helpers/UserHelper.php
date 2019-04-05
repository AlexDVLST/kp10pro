<?php

namespace App\Helpers;

use App\Models\User;

class UserHelper
{

    public static function generateDomain()
    {
        $findUser = false;
        do {
            $randomDomain = 'kp' . rand(10000000, 99999999);
            $findUser     = User::whereDomain($randomDomain)->first();
            // dd($findUser);
        } while ($findUser);

        return $randomDomain;
    }
}