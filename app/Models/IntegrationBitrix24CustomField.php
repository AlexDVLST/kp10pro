<?php

namespace App\Models;

use App\Scopes\IntegrationBitrix24CustomFieldScope;
use Illuminate\Database\Eloquent\Model;

class IntegrationBitrix24CustomField extends Model
{
    protected $fillable = [
        'account_id',
        'type',
        'bitrix24_field_name',
        'bitrix24_field_id',
        'bitrix24_field_type_id'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationBitrix24CustomFieldScope());
    }

    public function enums()
    {
        return $this->hasMany('App\Models\IntegrationBitrix24CustomFieldEnum', 'custom_field_id');
    }
}
