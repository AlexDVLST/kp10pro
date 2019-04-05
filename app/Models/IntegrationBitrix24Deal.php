<?php

namespace App\Models;

use App\Scopes\IntegrationBitrix24DealScope;
use Illuminate\Database\Eloquent\Model;

class IntegrationBitrix24Deal extends Model
{
    protected $fillable = [
        'account_id',
        'bitrix24_deal_id',
        'bitrix24_deal_responsible_user_id',
        'bitrix24_deal_stage_id'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationBitrix24DealScope);
    }

    /**
     * Amocrm fields
     *
     * @return IntegrationAmocrmLeadField
     */
    public function fields()
    {
        return $this->hasMany('App\Models\IntegrationBitrix24DealField', 'bitrix24_deal_id', 'bitrix24_deal_id');
    }
}
