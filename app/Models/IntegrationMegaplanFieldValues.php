<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use App\Scopes\IntegrationAmocrmUserScope;

class IntegrationMegaplanFieldValues extends Model
{
    protected $fillable = [
        'account_id',
        'field_id',
        'deal_id',
        'megaplan_field_values'
    ];
    
    // /**
    //  * The "booting" method of the model.
    //  *
    //  * @return void
    //  */
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new IntegrationMegaplanUser);
    // }

    public function field()
    {
        return $this->belongsTo('App\Models\IntegrationMegaplanField', 'field_id');
    }
}
