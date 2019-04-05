<?php

namespace App\Models;

use App\Scopes\IntegrationBitrix24DealFieldScope;
use Illuminate\Database\Eloquent\Model;

class IntegrationBitrix24DealField extends Model
{
    protected $fillable = [
        'account_id',
        'bitrix24_deal_id',
        'bitrix24_field_name',
        'bitrix24_field_id',
//        'bitrix24_field_is_system',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationBitrix24DealFieldScope);
    }

    /**
     * Bitrix24 field values
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function values()
    {
        return $this->hasMany('App\Models\IntegrationBitrix24DealFieldValue', 'deal_field_id', 'id');
    }

    /**
     * Bitrix24 custom field
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function customField()
    {
        return $this->hasOne('App\Models\IntegrationBitrix24CustomField', 'bitrix24_field_id', 'bitrix24_field_id');
    }
}
