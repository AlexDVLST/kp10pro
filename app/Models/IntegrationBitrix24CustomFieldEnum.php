<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationBitrix24CustomFieldEnum extends Model
{
    protected $fillable = [
        'custom_field_id',
        'bitrix24_enum_id',
        'bitrix24_enum_value',
    ];
}
