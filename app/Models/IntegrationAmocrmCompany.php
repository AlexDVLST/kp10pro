<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationAmocrmCompany extends Model
{
    protected $fillable = [
        'account_id',
        'company_id',
        'amocrm_company_id',
        'amocrm_company_name'
    ];
}
