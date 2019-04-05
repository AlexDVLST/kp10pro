<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferState extends Model
{
    protected $fillable = [
        'state_id',
        'offer_id'
    ];

    //Get system state data
    public function data()
    {
        return $this->belongsTo('App\Models\SystemOfferState', 'state_id');
    }

}
