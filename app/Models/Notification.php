<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\NotificationScope;

class Notification extends Model
{
    protected $fillable = [
        'account_id',
        'text',
        'type_id',
        'icon',
        'color',
        'view',
        'viewed'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new NotificationScope);
    }
}
