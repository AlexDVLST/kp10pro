<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferVariant extends Model
{
    protected $fillable = [
        'offer_id',
        'name',
        'price',
        'selected',
        'type',
        'tax',
        'recommended'
    ];

    /**
     * Products in variant
     *
     * @return OfferVariantProduct
     */
    public function products()
    {
        return $this->hasMany('App\Models\OfferVariantProduct', 'variant_id');
    }

    /**
     * Fields in variant
     *
     * @return OfferVariantFields
     */
    public function fields()
    {
        return $this->hasMany('App\Models\OfferVariantFields', 'variant_id');
    }

    /**
     * specialDiscounts in variant
     *
     * @return OfferVariantFields
     */
    public function specialDiscounts()
    {
        return $this->hasMany('App\Models\OfferVariantSpecialDiscount', 'variant_id');
    }

    /**
     * Insert default
     *
     * @param integer $id
     * @return void
     */
    public static function insertDefault(int $id)
    {
        self::insert(
            [
                [
                    'offer_id'    => $id,
                    'name'        => 'Вариант Стандартный',
                    'price'       => 35000,
                    'selected'    => 0,
                    'active'      => 1,
                    'tax'         => 0,
                    'type'        => 'economy',
                    'recommended' => 0
                ],
                [
                    'offer_id'    => $id,
                    'name'        => 'Вариант Оптимальный',
                    'price'       => 56000,
                    'selected'    => 0,
                    'active'      => 1,
                    'tax'         => 0,
                    'type'        => 'standard',
                    'recommended' => 1
                ],
                [
                    'offer_id'    => $id,
                    'name'        => 'Вариант Премиум',
                    'price'       => 85000,
                    'selected'    => 0,
                    'active'      => 1,
                    'tax'         => 0,
                    'type'        => 'premium',
                    'recommended' => 0
                ]
            ]
        );
    }
}
