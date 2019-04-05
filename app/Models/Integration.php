<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\IntegrationScope;

class Integration extends Model
{
    protected $fillable = [
        'account_id',
        'system_crm_id'
    ];
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IntegrationScope);
    }

        /**
     * Get current integration
     *
     * @return json
     */
    // public function integration()
    // {
    //     $IntegrationCrm      = false;
    //     $integration         = Integration::first();
    //     $crn                 = SystemCrm::whereId($integration->system_crm_id)->first();
    //     if ($crn->type == 'megaplan') {
    //         $IntegrationCrm = IntegrationMegaplan::first();
    //     }

    //     return response()->json($IntegrationCrm);
    // }

}
