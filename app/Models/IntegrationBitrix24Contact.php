<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationBitrix24Contact extends Model
{
    protected $fillable = [
        'account_id',
        'contact_id',
        'bitrix24_contact_id',
        'bitrix24_contact_name',
        'bitrix24_contact_company_id'
    ];
}
