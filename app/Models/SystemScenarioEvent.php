<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemScenarioEvent extends Model
{
    protected $table = 'system_scenario_events';

    protected $fillable = [
        'name'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function additionalEvents()
    {
        return $this->hasMany('App\Models\SystemScenarioAdditionalEvent', 'event_id', 'id');
    }
}
