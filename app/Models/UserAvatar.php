<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * App\Models\UserAvatar
 *
 * @property int $id
 * @property int|null $file_id
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\File|null $file
 * @property-read mixed $url
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAvatar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAvatar whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAvatar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAvatar whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserAvatar whereUserId($value)
 * @mixin \Eloquent
 */
class UserAvatar extends Model
{
    protected $fillable = ['file_id', 'user_id'];

    public function file()
    {
        return $this->belongsTo('App\Models\File', 'file_id', 'id');
    }

    public function getUrlAttribute()
    {
        $file = $this->file;

        $url  = '';

        if ($file) {
            $url = $file->path . '/' . $file->file;
        } else {
            $url = 'public/resource/no-avatar.png';
        }

        return Storage::url($url);
    }
}
