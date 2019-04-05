<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductCustomFieldValue
 *
 * @property int $id
 * @property int $product_id
 * @property int $product_custom_field_id
 * @property string|null $value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomFieldValue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomFieldValue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomFieldValue whereProductCustomFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomFieldValue whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomFieldValue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ProductCustomFieldValue whereValue($value)
 * @mixin \Eloquent
 */
class ProductCustomFieldValue extends Model
{
    protected $table = 'product_custom_fields_value';
}
