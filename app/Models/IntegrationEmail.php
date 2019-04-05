<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationEmailScope;

class IntegrationEmail extends Model
{
    protected $fillable = [
        'smtp_login',
        'smtp_password',
        'smtp_server',
        'smtp_port',
        'smtp_secure',
        'user_id',
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

        static::addGlobalScope(new IntegrationEmailScope);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
