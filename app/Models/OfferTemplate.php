<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferTemplate extends Model
{
    protected $fillable = [
        'offer_id',
        'name',
        'version',
    ];
}
