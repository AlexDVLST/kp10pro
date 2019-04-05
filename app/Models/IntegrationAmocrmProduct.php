<?php

namespace App\Models;

use App\Scopes\IntegrationAmocrmProductScope;
use Illuminate\Database\Eloquent\Model;

class IntegrationAmocrmProduct extends Model
{
    protected $fillable = [
        'account_id',
        'product_id',
        'amocrm_product_id'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationAmocrmProductScope());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function productInfo()
    {
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }
}
