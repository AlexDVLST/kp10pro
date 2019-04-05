<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScenarioEvent extends Model
{
    protected $table = 'scenario_events';

    protected $fillable = [
        'scenario_id',
        'event_value',
        'event_type'
    ];
}
