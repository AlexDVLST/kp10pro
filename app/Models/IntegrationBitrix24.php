<?php

namespace App\Models;

use App\Scopes\IntegrationBitrix24Scope;
use Illuminate\Database\Eloquent\Model;

class IntegrationBitrix24 extends Model
{
    protected $table = 'integration_bitrix24';

    protected $fillable = [
        'host',
        'access_token',
        'refresh_token',
        'account_id',
        'application_token'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationBitrix24Scope());
    }

    /**
     * Get bitrix24 users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bitrix24Users()
    {
        return $this->hasMany('App\Models\IntegrationBitrix24User', 'account_id', 'account_id');
    }
}
