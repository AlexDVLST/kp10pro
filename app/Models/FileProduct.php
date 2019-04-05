<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * App\Models\FileProduct
 *
 * @property int $id
 * @property int $product_id
 * @property int $file_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FileProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FileProduct whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FileProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FileProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FileProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FileProduct extends Model
{
    protected $table = 'file_product';

    protected $fillable = [
        'product_id',
        'file_id',
    ];

    public function fileRelation()
    {
        return $this->belongsTo('App\Models\File', 'file_id');
    }

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

        return url(Storage::url($url));
    }
}
