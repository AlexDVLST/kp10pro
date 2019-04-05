<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationAmocrmCustomFieldScope;

class IntegrationAmocrmCustomField extends Model
{
    protected $fillable = [
        'account_id',
        'type',
        'amocrm_field_name',
        'amocrm_field_id',
        'amocrm_field_type_id',
    ];


     /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationAmocrmCustomFieldScope);
    }
    
    public function enums()
    {
        return $this->hasMany('App\Models\IntegrationAmocrmCustomFieldEnum', 'custom_field_id');
    }
}
