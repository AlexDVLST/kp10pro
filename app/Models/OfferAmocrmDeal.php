<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferAmoCrmDeal extends Model
{
    protected $table = 'offer_amocrm_deals';

    protected $fillable = ['offer_id', 'deal_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function data()
    {
        return $this->hasOne('App\Models\IntegrationAmocrmLead', 'amocrm_lead_id', 'deal_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function offerData()
    {
        return $this->hasOne('App\Models\Offer', 'id', 'offer_id');
    }
}
