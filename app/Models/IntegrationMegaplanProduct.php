<?php

namespace App\Models;

use App\Scopes\IntegrationMegaplanProductScope;
use Illuminate\Database\Eloquent\Model;

class IntegrationMegaplanProduct extends Model
{
    protected $fillable = [
        'account_id',
        'product_id',
        'megaplan_product_id'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationMegaplanProductScope());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function productInfo()
    {
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }
}
