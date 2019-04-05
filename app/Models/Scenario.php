<?php

namespace App\Models;

use App\Scopes\ScenarioScope;
use Illuminate\Database\Eloquent\Model;

class Scenario extends Model
{
    protected $table = 'scenario';

    protected $fillable = [
        'account_id',
        'event_id',
        'additional_event_id',
        'action_id'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ScenarioScope());
    }

    /**
     * Get scenario event
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function scenarioEvent()
    {
        return $this->hasOne('App\Models\SystemScenarioEvent', 'id', 'event_id');
    }

    /**
     * Get scenario system action
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function scenarioAction()
    {
        return $this->hasOne('App\Models\SystemScenarioAction', 'id', 'action_id');
    }

    /**
     * Get actions information
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany('App\Models\ScenarioAction', 'scenario_id', 'id');
    }

    /**
     * Get events information
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany('App\Models\ScenarioEvent', 'scenario_id', 'id');
    }
}
