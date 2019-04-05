<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferVariantSpecialDiscount extends Model
{
    protected $fillable = [
        'variant_id',
        'name',
        'index',
        'value'
    ];
}
