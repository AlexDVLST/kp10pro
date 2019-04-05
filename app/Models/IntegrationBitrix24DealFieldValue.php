<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationBitrix24DealFieldValue extends Model
{
    protected $fillable = [
        'deal_field_id',
        'bitrix24_field_value',
        'bitrix24_field_enum_id'
    ];
}
