<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationAmocrmUserScope;

class IntegrationAmocrmUser extends Model
{
    protected $fillable = [
        'account_id',
        'user_id',
        'amocrm_user_id',
        'amocrm_user_name',
        'amocrm_user_last_name',
        'amocrm_user_login'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationAmocrmUserScope);
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

}
