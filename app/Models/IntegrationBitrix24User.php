<?php

namespace App\Models;

use App\Scopes\IntegrationBitrix24UserScope;
use Illuminate\Database\Eloquent\Model;

class IntegrationBitrix24User extends Model
{
    protected $fillable = [
        'account_id',
        'user_id',
        'bitrix24_user_id',
        'bitrix24_user_name',
        'bitrix24_user_last_name',
        'bitrix24_user_login'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationBitrix24UserScope());
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
