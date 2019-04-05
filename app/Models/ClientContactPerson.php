<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientContactPerson extends Model
{
    protected $table = 'client_contact_persons';

    protected $fillable = [
        'client_contact_person_id',
        'client_id'
    ];

    public function clientRelation()
    {
        return $this->belongsTo('App\Models\Client', 'client_contact_person_id');
    }
}
