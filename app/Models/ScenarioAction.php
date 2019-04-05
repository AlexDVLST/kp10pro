<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScenarioAction extends Model
{
    protected $table = 'scenario_actions';

    protected $fillable = [
        'scenario_id',
        'action_value',
        'action_type'
    ];
}
