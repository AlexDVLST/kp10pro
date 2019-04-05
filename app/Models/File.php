<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Scopes\FileScope;

/**
 * App\Models\UserMeta
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $file
 * @property string $path
 * @property string $account
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereUpdatedAt($value)
 * @property int|null $cropped
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereCropped($value)
 */
class File extends Model
{
    protected $fillable = [
        'name',
        'file',
        'path',
        'account_id',
        'cropped', //if file was edited with cropperjs
    ];

     /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new FileScope);
    }
}
