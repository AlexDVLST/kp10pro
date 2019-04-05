<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Help extends Model
{
    protected $fillable = [
        'name',
        'video',
        'section_id',
        'external_link',
        'creator_user_id',
        'editor_user_id',
    ];

    /**
     * Get section relation
     *
     * @return HelpSection
     */
    public function section()
    {
        return $this->belongsTo(HelpSection::class);
    }

    /**
     * Get creator relation
     *
     * @return User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    /**
     * Get editor relation
     *
     * @return User
     */
    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_user_id');
    }
}
