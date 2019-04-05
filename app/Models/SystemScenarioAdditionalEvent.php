<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemScenarioAdditionalEvent extends Model
{
    protected $table = 'system_scenario_additional_events';

    protected $fillable = [
        'event_id',
        'additional_event_name',
        'additional_event_value',
        'type'
    ];
}
