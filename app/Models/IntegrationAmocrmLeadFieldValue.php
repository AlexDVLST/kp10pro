<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationAmocrmLeadFieldValue extends Model
{
    protected $fillable = [
        'lead_field_id',
        'amocrm_field_value',
        'amocrm_field_enum_id',
    ];

}
