<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationUserIntegrationAmocrmUser extends Model
{
    protected $fillable = [
        'account_id',
        'user_id',
        'amocrm_user_id'
    ];
}
