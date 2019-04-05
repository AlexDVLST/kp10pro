<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPhone;
use App\Models\UserPosition;
use App\Models\UserSignature;
use App\Models\UserAvatar;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegistered;
use Illuminate\Support\Facades\Log;

/**
 *
 */
trait EmployeeTrait
{
    public function addEmployee($account, Request $request, $sendEmail = true)
    {
        //Validate input data
        $request->validate([
            'surname'          => 'required|string|max:255',
            'name'             => 'required|string|max:255',
            'middleName'       => 'nullable|string|max:255',
            'email'            => 'required|unique:users,email,NULL,id,domain,' . $account, //validate unique by email, domain columns
            'phone'            => 'nullable|phone',
            'position'         => 'nullable|string',
            'signature'        => 'nullable|string',
            'fileId'           => 'nullable|numeric'
        ]);

        $surname            = $request->input('surname');
        $name               = $request->input('name');
        $middleName         = $request->input('middleName');
        $email              = $request->input('email');
        $phone              = preg_replace('/[^0-9]/', '', $request->input('phone'));
        $position           = $request->input('position');
        $signature          = $request->input('signature');
        $fileId             = $request->input('fileId');
        $password           = str_random(8);

        $user = User::create([
            'domain'          => $account,
            'surname'         => $surname,
            'name'            => $name,
            'middle_name'     => $middleName,
            'email'           => $email,
            'password'        => bcrypt($password),
        ]);

        //Assign role to new user
        $user->assignRole('employee');

        //Save phone
        $user->phoneRelation()
                ->save(new UserPhone(['phone' => $phone]));

        //Save position
        $user->positionRelation()
                ->save(new UserPosition(['position' => $position]));

        //Save signature
        $user->signatureRelation()
                ->save(new UserSignature(['signature' => $signature]));

        //Save avatar
        $user->avatarRelation()
                ->save(new UserAvatar(['file_id' => $fileId]));

        if ($sendEmail) {
            $user->originalPassword = $password;
            //Send mail to registered user
            Mail::to($email)->send(new UserRegistered($user));
        }

        return $user;
    }
}
