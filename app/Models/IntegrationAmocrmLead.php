<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationAmocrmLeadScope;

class IntegrationAmocrmLead extends Model
{
    protected $fillable = [
        'account_id',
        'amocrm_lead_id',
        'amocrm_lead_responsible_user_id',
        'amocrm_lead_status_id',
        'amocrm_lead_sale',
    ];

    /**
    * The "booting" method of the model.
    *
    * @return void
    */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationAmocrmLeadScope);
    }

    /**
     * Amocrm fields
     *
     * @return IntegrationAmocrmLeadField
     */
    public function fields()
    {
        return $this->hasMany('App\Models\IntegrationAmocrmLeadField', 'amocrm_lead_id', 'amocrm_lead_id');
    }
}
