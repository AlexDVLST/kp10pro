<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\OfferScope;

//grapesjs templates

/**
 * App\Models\Offer
 */
class Offer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'gjs_assets',
        'gjs_components',
        'gjs_css',
        'gjs_html',
        'gjs_styles',
        'offer_name',
        'user_id',
        'url',
        'system',
        'parent_template_id',
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

        static::addGlobalScope(new OfferScope);
    }

    /**
     * Get relation with offer_numbers table
     *
     * @return OfferNumber
     */
    public function numberRelation()
    {
        return $this->hasOne('App\Models\OfferNumber');
    }

    /**
     * Get client relatiion
     *
     * @return OfferClient
     */
    public function clientRelation()
    {
        return $this->hasOne('App\Models\OfferClient');
    }

    /**
     * Get contact person
     *
     * @return OfferContactPerson
     */
    public function contactPersonRelation()
    {
        return $this->hasOne('App\Models\OfferContactPerson');
    }

    /**
     * Get user who create offer
     *
     * @return User
     */
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    /**
     * Get state relation
     *
     * @return OfferState
     */
    public function state()
    {
        return $this->hasOne('App\Models\OfferState');
    }

    /**
     * Get state history
     *
     * @return OfferStateHistory
     */
    public function stateHistory()
    {
        return $this->hasMany('App\Models\OfferStateHistory');
    }

    /**
     * Get variants
     *
     * @return OfferVariant
     */
    public function variants()
    {
        return $this->hasMany('App\Models\OfferVariant');
    }

    /**
     * Get user template settings
     *
     * @return OfferUserTemplate
     */
    public function userTemplate()
    {
        return $this->hasOne('App\Models\OfferUserTemplate');
    }

    /**
     * Get based on template information
     *
     * @return OfferTemplate
     */
    public function template()
    {
        return $this->hasOne('App\Models\OfferTemplate');
    }

    /**
     * Get employee of the offer
     *
     * @return OfferEmployee
     */
    public function employee()
    {
        return $this->hasOne('App\Models\OfferEmployee');
    }

    /**
     * Get amoCRM deals relative to offer
     *
     * @return OfferAmocrmDeal
     */
    public function amocrmDeal()
    {
        return $this->hasOne('App\Models\OfferAmocrmDeal');
    }

    /**
     * Get amoCRM deals relative to offer
     *
     * @return OfferAmocrmDeal
     */
    public function megaplanDeal()
    {
        return $this->hasOne('App\Models\OfferMegaplanDeal');
    }

    /**
     * Get Bitrix24 deals relative to offer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bitrix24Deal()
    {
        return $this->hasOne('App\Models\OfferBitrix24Deal');
    }

    /**
     * Get currency deals relative to offer
     *
     * @return OfferCurrency
     */
    public function currency()
    {
        return $this->hasOne('App\Models\OfferCurrency');
    }

    /**
     * Get number of the offer
     *
     * @return string
     */
    public function getNumberAttribute()
    {
        return $this->numberRelation->number;
    }

    /**
     * Get client id
     *
     * @return int
     */
    public function getClientIdAttribute()
    {
        return $this->clientRelation ? $this->clientRelation->client_id : 0;
    }

    /**
     * Get contact person id
     *
     * @return int
     */
    public function getContactPersonIdAttribute()
    {
        return $this->contactPersonRelation ? $this->contactPersonRelation->client_id : 0;
    }

    /**
     * Get is user template attribute
     *
     * @return int
     */
    public function getIsUserTemplateAttribute()
    {
        return $this->userTemplate ? $this->userTemplate->is_template : 0;
    }
}
