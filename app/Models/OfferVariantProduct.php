<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OfferVariantProduct extends Model
{
    protected $fillable = [
        'offer_id',
        'product_id',
        'group',
        'description',
        'fake_product_id',
        'variant_id',
        'index',
        'image',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function values()
    {
        return $this->hasMany('App\Models\OfferVariantProductValues', 'variant_product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product()
    {
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bitrix24Product()
    {
        return $this->hasOne('App\Models\IntegrationBitrix24Product', 'product_id', 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function megaplanProduct()
    {
        return $this->hasOne('App\Models\IntegrationMegaplanProduct', 'product_id', 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function amocrmProduct()
    {
        return $this->hasOne('App\Models\IntegrationAmocrmProduct', 'product_id', 'product_id');
    }
}
