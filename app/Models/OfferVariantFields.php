<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferVariantFields extends Model
{
    protected $fillable = [
        'variant_id',
        'name',
        'index',
        'type'
    ];

}
