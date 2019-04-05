<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\ProductScope;
use App\Models\Currency;

/**
 * App\Product
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $account
 * @property string $name
 * @property string $article
 * @property string $prod_description
 * @property float $cost
 * @property float $prime_cost
 * @property int|null $removed Флаг удалённого коммерческого предложения
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductCustomField[] $productcustomfield
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereArticle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product wherePrimeCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereProdDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereRemoved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereUpdatedAt($value)
 * @property string $description
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FileProduct[] $file
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductCustomField[] $productCustomField
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Product whereDescription($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductCustomFieldValue[] $productCustomFieldValue
 */
class Product extends Model
{
    protected $fillable = [
        'id',
        'account_id',
        'name',
        'article',
        'cost',
        'prime_cost',
        'description'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ProductScope);
    }

    public function productCustomFieldValue()
    {
        return $this->hasMany('App\Models\ProductCustomFieldValue');
    }

    public function file()
    {
        return $this->hasMany('App\Models\FileProduct');
    }
}
