<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferStateHistory extends Model
{
    protected $fillable = [
        'state_id',
        'offer_id',
        'user_id'
    ];
}
