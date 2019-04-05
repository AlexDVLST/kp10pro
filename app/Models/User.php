<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\User
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserMeta[] $meta
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User role($roles)
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $domain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\File[] $file
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 * @property string $surname
 * @property string|null $middle_name
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\UserAvatar $avatarRelation
 * @property-read mixed $avatar_url
 * @property-read mixed $display_name
 * @property-read mixed $phone
 * @property-read mixed $position
 * @property-read mixed $signature
 * @property-read \App\Models\UserPhone $phoneRelation
 * @property-read \App\Models\UserPosition $positionRelation
 * @property-read \App\Models\UserSignature $signatureRelation
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSurname($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User withoutTrashed()
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain',
        'surname',
        'name',
        'middle_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * User meta table
     */
    public function meta()
    {
        return $this->hasMany('App\Models\UserMeta');
    }

    /**
     * Get user phones
     */
    public function phoneRelation()
    {
        return $this->hasOne('App\Models\UserPhone');
    }

    /**
     * Get user positions
     */
    public function positionRelation()
    {
        return $this->hasOne('App\Models\UserPosition');
    }

    /**
     * Get user signatures
     */
    public function signatureRelation()
    {
        return $this->hasOne('App\Models\UserSignature');
    }

    /**
     * Get user avatar
     */
    public function avatarRelation()
    {
        return $this->hasOne('App\Models\UserAvatar');
    }

    public function amocrm()
    {
        return $this->hasOne('App\Models\IntegrationAmocrmUser');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function megaplan()
    {
        return $this->hasOne('App\Models\IntegrationMegaplanUser');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function bitrix24()
    {
        return $this->hasOne('App\Models\IntegrationBitrix24User');
    }

    /**
     * Get email integration
     *
     * @return IntegrationEmail
     */
    public function smtpEmails()
    {
        return $this->hasMany('App\Models\IntegrationEmail');
    }

    /**
     * Get admin of the account
     *
     * @return User
     */
    public function getAdminAttribute()
    {
        return User::whereId($this->accountId)->first();
    }

    /**
     * Get display name for user
     */
    public function getDisplayNameAttribute()
    {
        $displayName = '';

        if ($this->surname) {
            $displayName .= $this->surname . ' ';
        }

        if ($this->name) {
            $displayName .= $this->name . ' ';
        }

        if ($this->middle_name) {
            $displayName .= $this->middle_name . ' ';
        }

        return $displayName;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhoneAttribute()
    {
        return $this->phoneRelation->phone;
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
     * Get signature
     *
     * @return string
     */
    public function getSignatureAttribute()
    {
        return $this->signatureRelation->signature;
    }

    /**
     * Get avatar url
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        return $this->avatarRelation->url;
    }

    /**
     * Get account id
     *
     * @return int
     */
    public function getAccountIdAttribute()
    {
        return $this->role('user')->whereDomain($this->domain)->first()->id;
    }

    /**
     * Get permission for user
     *
     * @return int
     */
    public function userCan($permission)
    {
        // search own permission
        $strpos = strpos($permission,"-own");
        // if find check permission for user (not admin)
        if ($strpos !== false) {
            $parsPermission = str_replace("-own","",$permission);
            if (!$this->hasRole('user') && $this->can($permission) && !$this->can($parsPermission)) {
                return true;
            }
            return false;
        }
        return $this->can($permission);
    }

    public function isOnline()
    {
        return Cache::has('user-is-online-' . $this->id);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
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
     * Check if current user is account admin
     *
     * @return bool
     */
    public function getIsAccountAdminAttribute()
    {
        return $this->accountId === $this->id;
    }
}
