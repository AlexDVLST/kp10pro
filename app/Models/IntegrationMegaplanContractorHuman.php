<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationAmocrmUserScope;

class IntegrationMegaplanContractorHuman extends Model
{
    protected $table = 'integration_megaplan_contractor_humans';

    protected $fillable = [
        'human_id',
        'account_id',
        'megaplan_human_id',
        'megaplan_human_name'
    ];

    // /**
    //  * The "booting" method of the model.
    //  *
    //  * @return void
    //  */
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new IntegrationMegaplanContractorHuman);
    // }
}
