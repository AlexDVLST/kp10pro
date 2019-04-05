<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationScope;
use App\Scopes\IntegrationMegaplanProgramScope;

class IntegrationMegaplanProgram extends Model
{
    protected $fillable = [
        'program_name',
        'program_id',
        'account_id'
    ];
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationMegaplanProgramScope);
    }
}
