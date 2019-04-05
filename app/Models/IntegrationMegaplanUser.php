<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use App\Scopes\IntegrationAmocrmUserScope;

class IntegrationMegaplanUser extends Model
{
    protected $fillable = [
        'user_id',                  
        'account_id',               
        'megaplan_user_id',         
        'megaplan_user_name',      
        'megaplan_user_middle_name',
        'megaplan_user_last_name'  
    ];

    // /**
    //  * The "booting" method of the model.
    //  *
    //  * @return void
    //  */
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new IntegrationMegaplanUser);
    // }
}
