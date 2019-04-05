<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferCurrency extends Model
{
    protected $fillable = ['offer_id', 'currency_id'];

    public function data()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id');
    }

}
