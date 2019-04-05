<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Employee
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $domain
 * @property string $surname
 * @property string|null $middle_name
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Employee whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Employee extends Model
{
    protected $table = 'users';
}
