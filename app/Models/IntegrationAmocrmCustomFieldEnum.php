<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationAmocrmCustomFieldEnum extends Model
{
    protected $fillable = [
        'custom_field_id',
        'amocrm_enum_id',
        'amocrm_enum_value',
    ];
}
