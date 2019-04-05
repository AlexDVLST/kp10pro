<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserPhone
 *
 * @property int $id
 * @property string|null $phone
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserPhone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserPhone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserPhone wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserPhone whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserPhone whereUserId($value)
 * @mixin \Eloquent
 */
class UserPhone extends Model
{
    protected $fillable = ['phone', 'user_id'];
}
