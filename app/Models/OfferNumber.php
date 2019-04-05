<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OfferNumber
 *
 * @property int $id
 * @property string $number
 * @property int $offer_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OfferNumber whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OfferNumber whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OfferNumber whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OfferNumber whereOfferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OfferNumber whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OfferNumber extends Model
{
    protected $fillable = ['offer_id', 'number'];
}
