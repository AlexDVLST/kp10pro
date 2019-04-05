<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationMegaplanEnumValueScope;

class IntegrationMegaplanEnumValue extends Model
{
    protected $fillable = [
        'account_id',               
        'field_id',
        'megaplan_enum_values'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationMegaplanEnumValueScope);
    }
}
