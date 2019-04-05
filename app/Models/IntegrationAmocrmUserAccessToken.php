<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationAmocrmUserAccessTokenScope;

class IntegrationAmocrmUserAccessToken extends Model
{
    protected $fillable = [
        'amocrm_user_id',
        'access_token',
        'account_id'
    ];

     /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationAmocrmUserAccessTokenScope);
    }
}
