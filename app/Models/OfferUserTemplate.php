<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferUserTemplate extends Model
{
    protected $fillable = [
        'offer_id',
        'is_template'
    ];
}
