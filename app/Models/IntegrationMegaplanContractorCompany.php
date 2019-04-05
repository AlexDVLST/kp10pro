<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationAmocrmUserScope;

class IntegrationMegaplanContractorCompany extends Model
{
    protected $fillable = [
        'company_id',
        'account_id',
        'megaplan_company_id',
        'megaplan_company_name'
    ];

    // /**
    //  * The "booting" method of the model.
    //  *
    //  * @return void
    //  */
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new IntegrationMegaplanContractorCompany);
    // }
}
