<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationAmocrmLeadFieldScope;

class IntegrationAmocrmLeadField extends Model
{
    protected $fillable = [
        'account_id',
        'amocrm_lead_id',
        'amocrm_field_name',
        'amocrm_field_id',
        'amocrm_field_is_system',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationAmocrmLeadFieldScope);
    }

    /**
     * Amocrm field values
     *
     * @return IntegrationAmocrmLeadFieldValue
     */
    public function values()
    {
        return $this->hasMany('App\Models\IntegrationAmocrmLeadFieldValue', 'lead_field_id', 'id');
    }

    /**
     * Amocrm custom field
     *
     * @return IntegrationAmocrmCustomField
     */
    public function customField()
    {
        return $this->hasOne('App\Models\IntegrationAmocrmCustomField', 'amocrm_field_id', 'amocrm_field_id');
    }
}
