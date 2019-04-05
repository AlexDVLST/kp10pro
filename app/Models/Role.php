<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as sRole;

class Role extends sRole
{
    /**
     * Get translation ro roles
     *
     * @return RoleTranslation
     */
    public function translationRelation()
    {
        return $this->hasOne('App\Models\RoleTranslation');
    }

    /**
     * Get translation for role
     *
     * @return string
     */
    public function getTranslationAttribute()
    {
        return $this->translationRelation->translation;
    }
}
