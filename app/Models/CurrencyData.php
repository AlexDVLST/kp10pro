<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CurrencyData
 *
 * @property int $id
 * @property string|null $char_code
 * @property int|null $code
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float|null $rate
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CurrencyData whereCharCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CurrencyData whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CurrencyData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CurrencyData whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CurrencyData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CurrencyData whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CurrencyData whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CurrencyData extends Model
{
    protected $table = 'currencies_data';
}
