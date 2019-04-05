<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\CurrencyScope;

/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string $account
 * @property string|null $name
 * @property string|null $code
 * @property int|null $sync
 * @property float|null $rate
 * @property string|null $sign
 * @property int $basic
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\CurrencyData $system
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Currency whereBasic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Currency whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Currency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Currency whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Currency whereSign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Currency whereSync($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Currency whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Currency extends Model
{

    protected $table = 'currencies';

    protected $fillable = [
        'name',
        'code',
        'rate',
        'description',
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

        static::addGlobalScope(new CurrencyScope);
    }

    public function system()
    {
        return $this->hasOne('App\Models\CurrencyData', 'code', 'code');
    }
}
