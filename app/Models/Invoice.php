<?php

namespace App\Models;

use SanderVanHooft\Invoicable\Invoice as sInvoice;

class Invoice extends sInvoice
{
    /**
     * Get status translation
     *
     * @return string
     */
    public function getStatusTranslationAttribute()
    {
        switch ($this->status) {
            case 'ready':
                return 'Готов к оплате';
            case 'waiting':
                return 'Ожидание';
            case 'paid':
                return 'Оплачен';
            case 'cancelled':
                return 'Отменен';
        }
    }

    /**
     * Check status
     *
     * @return boolean
     */
    public function getIsReadyAttribute()
    {
        return $this->status === 'ready';
    }

    /**
     * Check status
     *
     * @return boolean
     */
    public function getIsWaitingAttribute()
    {
        return $this->status === 'waiting';
    }

    /**
     * Check status
     *
     * @return boolean
     */
    public function getIsPaidAttribute()
    {
        return $this->status === 'paid';
    }

    /**
     * Check status
     *
     * @return boolean
     */
    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Change invoice status
     *
     * @return boolean
     */
    public function setPaid()
    {
        $this->status = 'paid';
        return $this->save();
    }
    
    /**
     * Change invoice status
     *
     * @return boolean
     */
    public function setWaiting()
    {
        $this->status = 'waiting';
        return $this->save();
    }

    /**
     * Change invoice status
     *
     * @return boolean
     */
    public function setCancelled()
    {
        $this->status = 'cancelled';
        return $this->save();
    }
}
