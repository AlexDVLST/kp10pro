<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferClient extends Model
{
    protected $fillable = ['offer_id', 'client_id'];

    /**
     * Get client
     *
     * @return Client
     */
    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }
}
