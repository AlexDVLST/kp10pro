<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDescription extends Model
{
    protected $fillable = [
        'client_id',
        'description'
    ];
}
