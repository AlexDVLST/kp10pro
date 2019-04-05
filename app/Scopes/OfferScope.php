<?php

namespace App\Scopes;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OfferScope implements Scope
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
            if ($user->userCan('view-own offer')) {
                $builder->where(function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id)
                        ->orWhereHas('userTemplate', function ($query) {
                            $query->where('is_template', '=', '1');
                        })
                        ->orWhere('system', '=', 1);
                });
            }
        }
    }
}
