<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ProductCustomFieldScope;

/**
 * App\Models\ProductCustomField
 *
 * @property int $id
 * @property string $account
 * @property string|null $name
 * @property string $type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $product
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomField whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductCustomField extends Model
{
    protected $fillable = [
        'id', 'name', 'type', 'account_id'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ProductCustomFieldScope);
    }
}
