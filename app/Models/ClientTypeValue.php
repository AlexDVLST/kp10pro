<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientTypeValue extends Model
{
    protected $fillable = [
        'client_id',
        'client_type_id'
    ];

    /**
     * Get type name relation
     *
     * @return ClientType
     */
    public function typeRelation()
    {
        return $this->belongsTo('App\Models\ClientType', 'client_type_id');
    }

    /**
     * Get type name
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->typeRelation->name;
    }
}
