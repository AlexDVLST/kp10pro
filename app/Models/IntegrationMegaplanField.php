<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationMegaplanFieldScope;

class IntegrationMegaplanField extends Model
{
    protected $fillable = [
        'field_name',
        'field_id',
        'account_id',
        'field_type_id',
        'content_type',
        'program_id'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationMegaplanFieldScope);
    }

    /**
     * Enum values
     *
     * @return IntegrationMegaplanEnumValue
     */
    public function enums()
    {
        return $this->hasMany('App\Models\IntegrationMegaplanEnumValue', 'field_id');
    }

    /**
     * Content types
     *
     * @return IntegrationMegaplanContentTypes
     */
    public function contentTypes()
    {
        return $this->hasMany('App\Models\IntegrationMegaplanContentTypes', 'field_id');
    }

    /**
     * Get program
     *
     * @return IntegrationMegaplanProgram
     */
    public function program()
    {
        return $this->hasOne('App\Models\IntegrationMegaplanProgram', 'program_id', 'program_id');
    }
}
