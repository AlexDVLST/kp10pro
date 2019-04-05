<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferMegaplanDeal extends Model
{
    protected $table = 'offer_megaplan_deals';

    protected $fillable = [
        'offer_id',
        'deal_id',
        'account_id'
    ];

    /**
     * Deal data
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function data()
    {
        return $this->hasOne('App\Models\IntegrationMegaplanDeal', 'megaplan_deal_id', 'deal_id');
    }

    /**
     * Deal field values
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function values()
    {
        return $this->hasMany('App\Models\IntegrationMegaplanFieldValues', 'deal_id', 'deal_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function offerData()
    {
        return $this->hasOne('App\Models\Offer', 'id', 'offer_id');
    }
}
