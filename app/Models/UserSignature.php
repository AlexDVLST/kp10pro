<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserSignature
 *
 * @property int $id
 * @property string|null $signature
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserSignature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserSignature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserSignature whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserSignature whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserSignature whereUserId($value)
 * @mixin \Eloquent
 */
class UserSignature extends Model
{
    protected $fillable = ['signature', 'user_id'];
}
