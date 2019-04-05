<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationClientIntegrationAmocrmCompany extends Model
{
    protected $fillable = [
        'account_id',
        'client_id',
        'amocrm_company_id'
    ];
}
