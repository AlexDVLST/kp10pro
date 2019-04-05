<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemScenarioAction extends Model
{
    protected $table = 'system_scenario_actions';

    protected $fillable = [
        'name'
    ];
}
