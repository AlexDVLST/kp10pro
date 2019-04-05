<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferContactPerson extends Model
{
    protected $table = 'offer_contact_persons';
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
