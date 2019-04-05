<?php

namespace App\Models;

use App\Scopes\IntegrationBitrix24ProductScope;
use Illuminate\Database\Eloquent\Model;

class IntegrationBitrix24Product extends Model
{
    protected $fillable = [
        'account_id',
        'product_id',
        'bitrix24_product_id'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationBitrix24ProductScope());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function productInfo()
    {
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }
}
