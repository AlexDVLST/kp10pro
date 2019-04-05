<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationAmocrmContact extends Model
{
    protected $fillable = [
        'account_id',
        'contact_id',
        'amocrm_contact_id',
        'amocrm_contact_name',
        'amocrm_contact_responsible_user_id',
        'amocrm_contact_company_id'
    ];
}
