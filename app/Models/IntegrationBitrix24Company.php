<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationBitrix24Company extends Model
{
    protected $fillable = [
        'account_id',
        'company_id',
        'bitrix24_company_id',
        'bitrix24_company_name'
    ];
}
