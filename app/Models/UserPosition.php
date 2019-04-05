<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserPosition
 *
 * @property int $id
 * @property string|null $position
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserPosition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserPosition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserPosition wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserPosition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserPosition whereUserId($value)
 * @mixin \Eloquent
 */
class UserPosition extends Model
{
    protected $fillable = ['position', 'user_id'];
}
