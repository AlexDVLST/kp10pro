<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPosition extends Model
{
    protected $fillable = [
        'client_id',
        'position'
    ];
}
