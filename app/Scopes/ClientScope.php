<?php

namespace App\Scopes;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ClientScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $user = Auth::user();

            //Apply company
            $builder->whereAccountId($user->accountId);
            
            //Permissions
            //For own offers except User role
            // if (!$user->hasRole('user') && $user->can('view-own client') && !$user->can('view client')) {
            if ($user->userCan('view-own client')) {
                $builder->where(function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                })
                //Responsible
                ->orWhereHas('responsibleRelation', function($query) use ($user){
                    $query->whereIn('user_id', [$user->id]);
                });
            }
        }
    }
}
