<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferHistory extends Model
{
    protected $table = 'offer_histories';

    protected $fillable = [
        'account_id',
        'offer_id',
        'user_id',
        'client_id',
        'system_action_id'
    ];
}
