<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleTranslation extends Model
{
    protected $fillable = [
        'id', 'translation', 'role_id'
    ];

}
