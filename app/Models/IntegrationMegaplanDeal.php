<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationMegaplanDeal extends Model
{
    protected $fillable = [
        'account_id',
        'megaplan_deal_id',
        'megaplan_state_id',
        'megaplan_program_id'
    ];

    // public function values()
    // {
    //     return $this->hasMany('App\Models\IntegrationMegaplanFieldValues', 'deal_id', 'megaplan_deal_id');
    // }
}
