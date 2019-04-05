<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientEmail extends Model
{
    protected $fillable = [
        'client_id',
        'email',
        'default',
    ];
}
