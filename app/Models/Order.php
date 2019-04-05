<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use SanderVanHooft\Invoicable\IsInvoicable\IsInvoicableTrait;
use App\Scopes\OrderScope;
use App\Models\Invoice;

class Order extends Model
{
    // use IsInvoicableTrait;

    protected $fillable = [
        'user_id',
        'account_id',
        'licenses',
        'invoice_id',
        'tariff_id'
    ];

    protected $dates = ['started_at', 'expired_at'];

    /**
    * The "booting" method of the model.
    *
    * @return void
    */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new OrderScope);
    }

    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoicable');
    }

    /**
     * Get currenct invoice
     *
     * @return mixed
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get tariff
     *
     * @return Tariff
     */
    public function tariff()
    {
        return $this->belongsTo('App\Models\Tariff');
    }

    /**
     * Get order and invoice by reference
     *
     * @param string $reference
     * @return Order
     */
    public static function findByReference($reference)
    {
        return static::with(['invoices' => function ($query) use ($reference) {
            $query->where('reference', '=', $reference);
        }])->first();
    }

    /**
     * Get invoice by id
     *
     * @param int $id
     * @return void
     */
    public static function findByInvoiceId($id)
    {
        return static::with(['invoices' => function ($query) use ($id) {
            $query->where('id', '=', $id);
        }])->first();
    }

    public function isActive()
    {
    }
}
