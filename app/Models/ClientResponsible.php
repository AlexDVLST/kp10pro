<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientResponsible extends Model
{
    protected $fillable = [
        'client_id',
        'user_id'
    ];
}
