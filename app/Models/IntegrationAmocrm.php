<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationAmocrmScope;

class IntegrationAmocrm extends Model
{
    protected $table = 'integration_amocrm';

    protected $fillable = [
        'account_id',
        'host',
        'api_token',
        'login'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationAmocrmScope);
    }

    /**
     * Get amocrm users
     *
     * @return IntegrationAmocrmUser
     */
    public function amocrmUsers()
    {
        return $this->hasMany('App\Models\IntegrationAmocrmUser', 'account_id', 'account_id');
    }
}
