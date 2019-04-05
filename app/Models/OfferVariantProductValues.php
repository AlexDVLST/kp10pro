<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferVariantProductValues extends Model
{
    protected $fillable = [
        'variant_product_id',
        'index',
        'value',
        'type',
        'value_in_price'
    ];
}
