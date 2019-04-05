<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCompany extends Model
{
    protected $table = 'client_companies';

    protected $fillable = [
        'client_id',
        'client_company_id'
    ];

    public function clientRelation()
    {
        return $this->belongsTo('App\Models\Client', 'client_company_id');
    }

    public function getNameAttribute()
    {
        if (!$this->clientRelation) {
            return '';
        }
        return $this->clientRelation->name;
    }
}
