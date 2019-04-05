<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPhone extends Model
{
    protected $fillable = [
        'client_id',
        'phone',
        'default',
    ];
}
