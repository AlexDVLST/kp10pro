<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferBitrix24Deal extends Model
{
    protected $table = 'offer_bitrix24_deals';

    protected $fillable = ['offer_id', 'deal_id'];

    public function data()
    {
        return $this->hasOne('App\Models\IntegrationBitrix24Deal', 'bitrix24_deal_id', 'deal_id');
    }

    public function offerData()
    {
        return $this->hasOne('App\Models\Offer', 'id', 'offer_id');
    }
}
