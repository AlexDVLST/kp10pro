<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\UserMeta
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string $meta_key
 * @property string|null $meta_value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserMeta whereMetaKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserMeta whereMetaValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserMeta whereUserId($value)
 */
class UserMeta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_meta';

    protected $fillable = [
        'user_id',
        'meta_key',
        'meta_value'
    ];

    /**
     * Get meta_data by meta_key
     *
     * @param $meta_key
     * @param bool $meta_value
     *
     * @return mixed
     */
    public static function getMeta($metaKey, $metaValue = false)
    {
        $result = self::where([['meta_key', $metaKey], ['user_id', Auth::user()->id]])->first();

        //if only meta value response set
        if ($result && $metaValue) {
            $result = $result->metaValue;
        }

        return $result;
    }

    /**
     * @param $meta_key
     * @param $meta_value
     *
     * @return mixed
     */
    public static function updateMeta($metaKey, $metaValue)
    {
        return self::updateOrCreate(
            ['meta_key' => $metaKey, 'user_id' => Auth::user()->id],
            ['meta_value' => $metaValue]
        );
    }
}
