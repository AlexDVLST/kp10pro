<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\ClientScope;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'surname',
        'name',
        'middle_name',
        'user_id'
    ];

    /**
         * The "booting" method of the model.
         *
         * @return void
         */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ClientScope);
    }

    /**
     * Get type relation
     *
     * @return ClientType
     */
    public function typeValueRelation()
    {
        return $this->hasOne('App\Models\ClientTypeValue');
    }

    /**
     * Get descrition relation
     *
     * @return ClientDescription
     */
    public function descriptionRelation()
    {
        return $this->hasOne('App\Models\ClientDescription');
    }

    /**
     * Get phone relation
     *
     * @return ClientPhone
     */
    public function phoneRelation()
    {
        return $this->hasMany('App\Models\ClientPhone');
    }

    /**
     * Get email relation
     *
     * @return ClientPhone
     */
    public function emailRelation()
    {
        return $this->hasMany('App\Models\ClientEmail');
    }

    /**
     * Get position relation
     *
     * @return ClientPosition
     */
    public function positionRelation()
    {
        return $this->hasOne('App\Models\ClientPosition');
    }

    /**
     * Get position relation
     *
     * @return ClientResponsible
     */
    public function responsibleRelation()
    {
        return $this->hasMany('App\Models\ClientResponsible');
    }

    /**
     * Get contact person
     *
     * @return ClientContactPerson
     */
    public function contactPersonRelation()
    {
        return $this->hasMany('App\Models\ClientContactPerson');
    }

    /**
     * Get company relation
     *
     * @return ClientCompany
     */
    public function companyRelation()
    {
        return $this->hasOne('App\Models\ClientCompany');
    }

    /**
     * Formatting display name
     *
     * @return void
     */
    public function getDisplayNameAttribute()
    {
        $displayName = '';

        if ($this->surname && $this->typeId != 1) { //Except Company
            $displayName .= $this->surname . ' ';
        }

        if ($this->name) {
            $displayName .= $this->name . ' ';
        }

        if ($this->middle_name && $this->typeId != 1) { //Except Company
            $displayName .= $this->middle_name . ' ';
        }

        return $displayName;
    }

    /**
     * Get type name
     *
     * @return string
     */
    public function getTypeNameAttribute()
    {
        return $this->typeValueRelation->name;
    }

    /**
     * Get type id
     *
     * @return string
     */
    public function getTypeIdAttribute()
    {
        return $this->typeValueRelation->client_type_id;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescriptionAttribute()
    {
        return $this->descriptionRelation->description;
    }

    /**
     * Get phones
     *
     * @return array
     */
    public function getPhonesAttribute()
    {
        return $this->phoneRelation->all();
    }

    /**
     * Get emails
     *
     * @return array
     */
    public function getEmailsAttribute()
    {
        return $this->emailRelation->all();
    }

    /**
     * Get responsibles
     *
     * @return array
     */
    public function getResponsiblesAttribute()
    {
        return $this->responsibleRelation->all();
    }

    /**
     * Get contact person
     *
     * @return int
     */
    public function getContactPersonsAttribute()
    {
        return $this->contactPersonRelation;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPositionAttribute()
    {
        return $this->positionRelation->position;
    }

    /**
     * Get company id
     *
     * @return int
     */
    public function getCompanyIdAttribute()
    {
        return $this->companyRelation->client_company_id;
    }

    public function getCompanyNameAttribute()
    {
        return $this->companyRelation->name;
    }
}
